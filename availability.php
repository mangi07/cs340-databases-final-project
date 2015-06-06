<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 23, 2015
COURSE: CS 340 - Introduction to Databases, Oregon State University
*/


//check for login
if (!isset($_SESSION['user']) && !isset($_SESSION["user_type"])){
	echo "<div class='box'>You must be logged in to view this page.<br>
		<button onclick='window.location.href = \"index.php\"' class='button'>Log In</button></div>";
	die();
}

?>


<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>CS 340 Final Project - Ben R. Olson</title>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
	
	<!--jQuery link needed BEFORE trying to load calendar plugin based on jQuery!!-->
	<script type="text/javascript" src="jquery-1.8.3.min.js"></script>
	
	<!-- script for function show_sched -->
	<script type="text/javascript">
		$(document).ready(function() {
			$(".self").click(function(){
				if($(this).attr('class')=="self on"){
					$(this).attr('class', 'self off');
				}else if($(this).attr('class')=="self off"){
					$(this).attr('class', 'self on');
				}
			});
			
			//modified from http://www.htmlgoodies.com/beyond/css/working_w_tables_using_jquery.html
			function getSched() {
				var data = [];
				$(".sched").find('.self.dayrow').each(function (rowIndex, r) {
					var timeslots = "";
					$(this).find('.self').each(function (colIndex, c) {
						if($(this).attr('class')=="self on"){
							timeslots += "1";
						}else if($(this).attr('class')=="self off"){
							timeslots += "0";
						}
					});
					data.push(timeslots);
				});
				return data;
			}
			
			$("#edit_self").click(function(){
				
				var data = getSched();
				
				sched = JSON.stringify(data);
				console.log(sched);
				
				$("#sched").attr('value',sched);;
				
			});
			
			
		});

	</script>
	
</head>
<body class="centered">


<?php
    ini_set('display_errors', 'On');	

	echo "<div class='box'>";
	echo "<h1>ESL Tutoring Portal</h1>";
	echo "<h3>Created By Ben R. Olson</h3>";
	echo "<h2>Logged In As \"$_SESSION[user]\"</h2>";
	echo "<h2>Account Type: $_SESSION[user_type]</h2>";
	echo "</div>";


    include ("db.php");


	if(isset($_POST['sched']) && $_POST['sched'] != ""){
		$sched = json_decode($_POST['sched']);
		update_weekly_sched($sched);
	}
	
	$your_schedule = get_schedule("yours");
	
	if(in_array(null, $your_schedule)){
		$sched = array();
		for($i = 0; $i < 7; $i++){
			//fill schedule with timeslots all off
			$sched[] = "000000000000000000000000000000000000000000000000";
		}
		
		insert_weekly_sched($sched);
		$your_schedule = get_schedule("yours");
	}
	
	echo "<h3>Your Weekly Availability (time slots available are shown in green)</h3>";
	show_sched($your_schedule, "yours");
	
	if(isset($_POST['other_id']) && $_POST['other_id'] != ""){
		$other_schedule = get_schedule("other's");
		echo "<h3>Other Party's Weekly Availability (time slots available are shown in green)</h3>";
		show_sched($other_schedule, "other's");
		$intersect = find_sched_intersect($_SESSION['user'], $_POST['other_id']);
		echo "<h3>Schedule Intersect (Times that both of you have are shown in green.)</h3>";
		show_sched($intersect, "other's");
	}else{
		echo "<p>Try to find and request tutors to work on scheduling with.</p>\n
				<p>Go to the main page, filter for tutors, and wait for them to accept you.</p>\n
				<p>Then come back here with the selected tutor to see where your schedules intersect.</p>";
	}


	
  ?>

	<!-- link back to main.php -->
	<div class="button"><a href="main.php">Back To Main</a></div>
  
<?php

  /*
    Pre-conditions: $mysqli must exist as object connecting to database
	Arguments: $user_part is either "yours" or "other's"
	Returns: 7-element array to represent weekly schedule, each element being a daily string of 48 on/off time slots
  */
  function get_schedule($user_party){
    
	//create sql query string based on whose schedule we're looking for
	$sql = "select sun, mon, tues, wed, thurs, fri, sat from availability where ";
	$var; //either the user's user_name or the other party's id
	$bind; // either "s" or "i"
	if($user_party == "yours"){
		$sql .= "user_name = ?";
		$var = $_SESSION['user'];
		$bind = "s";
	}else if($user_party == "other's"){
		if($_SESSION['user_type']=='student'){$user_type='tutor';}
		if($_SESSION['user_type']=='tutor'){$user_type='student';}
		$sql .= "user_name = (select user_name from $user_type where id = ?)";
		$var = $_POST['other_id'];
		$bind = "i";
	}
	
	global $mysqli;
	
	$index = 0;
	$val = NULL;
	$sched = array();
	
    if(!($stmt = $mysqli->prepare($sql))){
	  echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param($bind,$var))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	if(!$stmt->bind_result($sched[0], $sched[1], $sched[2], $sched[3], $sched[4], $sched[5], $sched[6])){
		echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->fetch();
	$stmt->close();
	
	return $sched;
	
  }
  
  
  /*
    Pre-conditions: $stmt must exist as object connecting to database
	  $sched must be an array;
	Return: a string array of
	  time slots for each day, Sunday through Saturday,
	  where each day has 48 1/2-hour time slots
	  represented as a binary string (0 unavailabe, 1 available).
  */
  function find_sched_intersect($user1, $user2_id){
  
    global $mysqli;
	$index = 0;
	$intersect = array();
	$d = array();
	
	//figure out what table to use for the non-user's schedule
	if($_SESSION['user_type']=='student'){$user_type='tutor';}
	if($_SESSION['user_type']=='tutor'){$user_type='student';}
	
	//join tables between two users
	if(!($stmt = $mysqli->prepare("select tb1.*, tb2.* from
		(select sun, mon, tues, wed, thurs, fri, sat from availability where user_name = ?) as tb1
		inner join
		(select sun, mon, tues, wed, thurs, fri, sat from availability where user_name = (
			select user_name from $user_type where id = ? limit 1
		)) as tb2
		on 1;") )){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("ss",$user1,$user2_id))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	if(!$stmt->bind_result($d[0],$d[1],$d[2],$d[3],$d[4],$d[5],$d[6],
							$d[7],$d[8],$d[9],$d[10],$d[11],$d[12],$d[13])){
		echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->fetch();
	$stmt->close();
	
	for ($y = 0; $y < count($d)/2; $y++){
		$day = "";
		for ($z = 0; $z < strlen($d[$y]); $z++){
			if ($d[$y][$z] == $d[$y+7][$z] && $d[$y][$z] == '1')
				$day .= '1';
			else
				$day .= '0';
		}
		$intersect[$y] = $day;
	}
	
	return $intersect;
	
  }
  
  /*
    Pre-conditions: $days must contain an array of seven elements,
	  where each element is a binary string representing 48 30-minute time slots
	  in a given day.  The order of days in the array must be Sunday through Saturday.
	  The database should not contain a weekly schedule for the current user.
	  
	  $user_name holds the string value of the user_name for which to insert the schedule
	Post-conditions: The database contains a weekly schedule for that user.
  */
  function insert_weekly_sched(&$days){
    global $mysqli;
	
    if(!($stmt = $mysqli->prepare("insert into availability(
	  user_name, sun, mon, tues, wed, thurs, fri, sat) values (
	  ?, ?, ?, ?, ?, ?, ?, ?)") )){
	echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("ssssssss",$_SESSION['user'],
	  $days[0],$days[1],$days[2],$days[3],$days[4],$days[5],$days[6]))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->close();
  }
  
  /*
    Pre-conditions: $days must contain an array of seven elements,
	  where each element is a binary string representing 48 30-minute time slots
	  in a given day.  The order of days in the array must be Sunday through Saturday.
	  The database should contain a weekly schedule for the current user.
	  
	  $user_name holds the string value of the user_name for which to update the schedule
	Post-conditions: The database contains a weekly schedule for that user.
  */
  function update_weekly_sched(&$days){
    global $mysqli;
	
    if(!($stmt = $mysqli->prepare("update availability 
					set sun =   ?,
						mon =   ?,
						tues =  ?,
						wed =   ?,
						thurs = ?,
						fri =   ?,
						sat =   ?
						where user_name = ?") )){
	echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("ssssssss",
	  $days[0],$days[1],$days[2],$days[3],$days[4],$days[5],$days[6],$_SESSION['user']))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->close();
  }
  
    
  /*takes 7-element schedule array and shows it to the user if none of the elements are null*/
  function show_sched($sched_arr, $whose){
	foreach($sched_arr as $key => $val){
		if($val == null){
			echo "<p>No schedule to show.<br>\n";
			return;
		}
	}
	
	//set toggle ability on time slots if showing only the user's own schedule
	if($whose == "yours"){
		$ownership = "self";
	}else{
		$ownership = "";
	}
	
	$day_names = ["Sun.","Mon.","Tues.","Wed.","Thurs.","Fri.","Sat."];
	foreach($sched_arr as $key => $str){
		echo "<div class='sched'>";
		echo "<div class='$ownership dayrow'><span class='dayname'>$day_names[$key]</span>";
		for($i = 0; $i < strlen($str); $i++){
			//add labels to week rows here to identify time slots
			$hours_start = floor($i/2);
			if($i%2==0){
				$minutes_start = "00";
				$minutes_end = "30";
				$hours_end = $hours_start;
			}else{
				$minutes_start = "30";
				$minutes_end = "00";
				$hours_end = $hours_start + 1;
			}
			$hover_text = "$day_names[$key], $hours_start:$minutes_start to $hours_end:$minutes_end";
			if($str[$i] == '0') echo "<div class='$ownership off' title='$hover_text'></div>";
			if($str[$i] == '1') echo "<div class='$ownership on' title='$hover_text'></div>";
		}
		echo "</div>";
		echo "</div>";
	}
	//button to submit changes made to the user's schedule
	if($whose == "yours"){
		//this allows jquery to manipulate the form
		echo "<button id='edit_self' class='button'>Save Changes</button>";
		
		echo "<form action='availability.php' method='post'>\n
				<input id='sched' type='hidden' name='sched' value=''>\n";
		if(isset($_POST['other_id'])){
			echo "<input type='hidden' name='other_id' value='$_POST[other_id]'>\n";
		}
		echo "
				<p><input type='submit' id='edit_self' class='button' title='Click on \"Save Changes\" before \"Submit\" for changes to persist!'></p>\n
			</form>";
	}
	
	
	return;
  
  }

?>

</body>
</html>