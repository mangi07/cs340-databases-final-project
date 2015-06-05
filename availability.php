<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 23, 2015
COURSE: CS 340 - Web Development, Oregon State University
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
		});
		//add function to make day strings from self schedule 
		//  and submit self and check post to call php insert_weekly_sched()
		//  so check lines 80s...around there
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
	
	//debug
	//case: view_intersect, view_edit_self
	if(isset($_POST['case'])){
		if($_POST['case']=='view_intersect'){
			//check some more posts here:
			$other_id;
			if(isset($_POST['id'])){
				$other_id = $_POST['id'];
			}else{
				//error message
				
				///link to main, and then...
				
				die();
			}
			
			$your_schedule = get_schedule("yours");
			$other_schedule = get_schedule("other's");
			
			//show your schedule and the other party's schedule
			show_sched($your_schedule, "yours");
			show_sched($other_schedule, "other's");
			
			$intersect = find_sched_intersect();
			//show schedules and intersects with show_sched() function to write below
		}
		if($_POST['case']=='view_edit_self'){
			//add form here on this page to submit to this page with this case
			//do that: edit args to functions
			//get post from this page submitting to itself
			insert_weekly_sched();//only if post variables for schedule are correct
			$your_schedule = get_schedule();
			//check variable is not empty or something
			//show your schedule with show_sched() function to write below
		}
	}else{
		//error message here
	}
	
	
	/*
	$sched = array();
	$sched[0] = '000001111100000111110000011111000001111100000000';
	$sched[1] = '111110000011111000001111100000111110000011111111';
	$sched[2] = '000001111100000111110000011111000001111100000000';
	$sched[3] = '111110000011111000001111100000111110000011111111';
	$sched[4] = '000001111100000111110000011111000001111100000000';
	$sched[5] = '111110000011111000001111100000111110000011111111';
	$sched[6] = '000001111100000111110000011111000001111111111111';
	
	
	$user_name = 'frankie';
	$user2 = 'bobby';
	*/
	
	/*
	insert_weekly_sched($sched, $user2);
	*/
	
	/*
	$sched = find_sched_intersect($user_name, $user2);
	echo "INTERSECTION:\n";
	var_dump($sched);
	
	//try displaying schedule (receive as int, but then convert to binary string
    echo "USER SCHEDULE: \n";
	var_dump(get_schedule($user_name));
	*/
	
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
		$sql .= "user_name = (select user_name from users2 where id = ?)";
		$var = $_POST['id'];
		$bind = "i";
	}
	var_dump($sql);
	
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
	
	//join tables between two users
	if(!($stmt = $mysqli->prepare("select tb1.*, tb2.* from
		(select sun, mon, tues, wed, thurs, fri, sat from availability where user_name = ?) as tb1
		inner join
		(select sun, mon, tues, wed, thurs, fri, sat from availability where user_name = (
			select user_name from users2 where id = ? limit 1
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
	  
	  $user_name holds the string value of the user_name for which to insert the schedule
	Post-conditions: The database contains a weekly schedule for that user.
  */
  function insert_weekly_sched(&$days, $user_name){
    global $mysqli;
	
    if(!($stmt = $mysqli->prepare("insert into sched(
	  user_name, sun, mon, tues, wed, thurs, fri, sat) values (
	  ?, ?, ?, ?, ?, ?, ?, ?)") )){
	echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("ssssssss",$user_name,
	  $days[0],$days[1],$days[2],$days[3],$days[4],$days[5],$days[6]))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->close();
  }
  
  /*borrowed from mcampa at gmail dot com, posted on http://php.net/manual/en/function.decbin.php*/
  function d2b($n) {
    return str_pad(decbin($n), 48, "0", STR_PAD_LEFT);
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
	
	foreach($sched_arr as $key => $str){
		echo "<div class='dayrow'>";
		for($i = 0; $i < strlen($str); $i++){
			if($str[$i] == '0') echo "<div class='$ownership off'></div>";
			if($str[$i] == '1') echo "<div class='$ownership on'></div>";
		}
		echo "</div>";
	}
	//add button to submit for editing
	if($whose == "yours"){
		
	}
	
	
	return;
  
  }

?>

</body>
</html>