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
	
</head>
<body class="centered">


<?php
	echo "<div class='box'>";
	echo "<h1>ESL Tutoring Portal</h1>";
	echo "<h3>Created By Ben R. Olson</h3>";
	echo "<h2>Logged In As \"$_SESSION[user]\"</h2>";
	echo "<h2>Account Type: $_SESSION[user_type]</h2>";
	echo "</div>";
?>


	<!-- Logout functionality provided in main.js (160-166) is attached to this button: -->
	<div onclick="window.location.href = 'logout.php'" class="button">Log Out</div>
	<!--<div class="button"><a href="logout.php">Log Out</a></div>-->

<!-- Check mode here, a post variable (if set and what value)
mode: update_user, filter_tutors, set_schedule, request_tutor, accept_student 

note: add financial info (and sessions), display schedule, and tutor/student connections relevant to user 
note: insert into availability all 0's when creating a new user, use php to display schedule as styled table
note: add manager page to view how many students each tutor has, sessions, and financial info
-->


<?php
	/*refer to functions at the bottom of this page*/
	if (isset($_POST['mode']) && $_POST['mode'] = 'update_user' ){
		//update user function
		update_user();
	}
?>


<!-- View pending requests, relationships, and sessions or link to other php scripts with these things -->
<?php
	if($_SESSION['user_type']=="tutor"){
		//get and view pending requests from students...
		include("db.php");
		if(!($stmt = $mysqli->prepare("
			select s.fname, s.lname, s.year_born, s.gender, 
			s.start_date, s.end_date, s.max_rate, 
			s.first_lang, s.second_lang, s.id 
			from cs340final_project.student as s inner join	
			cs340final_project.student_wants_tutor as swt
			on s.id = swt.sid inner join
			cs340final_project.tutor as t
			on swt.tid = t.id
			where t.user_name = ?
			order by s.lname, s.fname
		"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("s",$_SESSION['user']))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
		}
		
		if(!$stmt->bind_result($fname, $lname, $year_born, $gender, $start_date, $end_date, $max_rate, $first_lang, $second_lang, $id)){
			echo "Bind paramaters failed: " . $stmt->errno . " " . $stmt->error;
		};
			
		//need to fetch each row here and accumulate into an array
		$students = array();
		while ($stmt->fetch()){
			$students[] = array("fname" => $fname, "lname" => $lname, "year_born" => $year_born, "gender" => $gender, "start_date" => $start_date, "end_date" => $end_date, "max_rate" => $max_rate, "first_lang" => $first_lang, "second_lang" => $second_lang, "id" => $id);
		}
		echo "<p>PENDING STUDENT REQUESTS:</p>";
		echo "<table><tr><th>First Name<th>Last Name<th>Year Born<th>Gender
			<th>Start Date<th>End Date<th>Max Rate<th>First Language<th>Second Language</tr>";
		
		foreach($students as $key => $val){
			echo "<tr>";
			
			//counter logic from http://stackoverflow.com/questions/1070244/how-to-determine-the-first-and-last-iteration-in-a-foreach-loop
			$i = 0;
			$len = count($val);
			foreach ($val as $k => $v) {
				if ($i < $len-1) {
					echo "<td>" . $v;
				}
				$i++;
			}
			
		}
		echo "</table>";
		
	}
?>

<?php if($_SESSION['user_type']=="tutor"):  ?>
	<!-- accept students here -->
	<form method="post" action="accept.php">
		<p>ACCEPT STUDENT FROM DROP-DOWN MENU</p>
		<select name="student_id">
		<?php
			foreach ($students as $key => $val){
				$student_id = $val['id'];
				$student_name = $val['fname'] . " " . $val['lname'];
				echo "<option value='$student_id'>$student_name</option>\n";
			}
		?>
		</select>
		<input type='submit'></input>
	</form>
<?php endif; ?>

<!-- Student sees relationships with tutors and related info, here. -->
<?php
if($_SESSION['user_type']=="student"){
	include("db.php");
	if(!($stmt = $mysqli->prepare("
		select t.fname, t.lname, t.year_born, t.gender, 
		t.start_date, t.end_date, t.min_rate, 
		t.first_lang, t.second_lang, t.id 
		from cs340final_project.student as s inner join	
		cs340final_project.student_tutor as st
		on s.id = st.sid inner join
		cs340final_project.tutor as t
		on st.tid = t.id
		where s.user_name = ?
		order by t.lname, t.fname
	"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("s",$_SESSION['user']))){
		echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
	}
	
	if(!$stmt->bind_result($fname, $lname, $year_born, $gender, $start_date, $end_date, $min_rate, $first_lang, $second_lang, $id)){
		echo "Bind paramaters failed: " . $stmt->errno . " " . $stmt->error;
	};
		
	//need to fetch each row here and accumulate into an array
	$tutors = array();
	while ($stmt->fetch()){
		$tutors[] = array("fname" => $fname, "lname" => $lname, "year_born" => $year_born, "gender" => $gender, "start_date" => $start_date, "end_date" => $end_date, "min_rate" => $min_rate, "first_lang" => $first_lang, "second_lang" => $second_lang, "id" => $id);
	}
	echo "<p>YOUR CURRENT TUTORS:</p>";
	echo "<table><tr><th>First Name<th>Last Name<th>Year Born<th>Gender
		<th>Start Date<th>End Date<th>Min Rate<th>First Language<th>Second Language</tr>";
	
	foreach($tutors as $key => $val){
		echo "<tr>";
		
		//counter logic from http://stackoverflow.com/questions/1070244/how-to-determine-the-first-and-last-iteration-in-a-foreach-loop
		$i = 0;
		$len = count($val);
		foreach ($val as $k => $v) {
			if ($i < $len-1) {
				echo "<td>" . $v;
			}
			$i++;
		}
		
	}
	echo "</table>";
		
}
?>

<!-- VIEW PERSONAL DATA -->
<h2>Personal Data</h2>
	<!-- Code in this div modified from filter.php example given in Module 8, CS 340, OSU -->
	<div class="box container">

<?php

//connect to database and make $mysqli object
include("db.php");

//craft the query string
if ($_SESSION["user_type"] == "student"){
	$rate_name = "max_rate ";
	$table_name = "student ";
} else if ($_SESSION["user_type"] == "tutor"){
	$rate_name = "min_rate ";
	$table_name = "tutor ";
}
$stmt_string = "SELECT fname, lname, year_born, gender, skype_id, start_date, end_date, $rate_name, first_lang, second_lang FROM cs340final_project.$table_name WHERE user_name = ?";

//debug statements
echo $stmt_string . "<br>";
echo $_SESSION['user'] . "<br>";

//example query string
//"SELECT fname, lname, year_born, gender, skype_id, start_date, end_date, min_rate, first_lang, second_lang FROM cs340final_project.tutor WHERE user_name = ?"

if(!($stmt = $mysqli->prepare($stmt_string))){
	echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
}
if(!($stmt->bind_param("s",$_SESSION['user']))){
	echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
}
if(!$stmt->execute()){
	echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
}
if(!$stmt->bind_result($fname, $lname, $year_born, $gender, $skype_id, $start_date, $end_date, $rate, $first_lang, $second_lang)){
	echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
}
$stmt->fetch();
$stmt->close();

echo "
	<form id=\"update_user\" action=\"?\" method=\"post\">\n 
		
		<p>First Name: $fname <br>Update: <input name=\"fname\" type=\"text\"></p>\n
		<p>Last Name: $lname <br>Update: <input name=\"lname\" type=\"text\"></p>\n
		<p>Year Born: $year_born <br>Update: <input name=\"year_born\" type=\"number\" min=\"1920\" max=\"2010\"></p>\n
		<p>Gender: $gender <br>Update: \n
			<select name=\"gender\">\n
				<option value=\"m\">Male</option>\n
				<option value=\"f\">Female</option>\n
			</select>\n
		</p>\n
		<p>Skype ID: $skype_id <br>Update: <input name=\"skype_id\" type=\"text\"></p>\n
		<p>Start Date: $start_date <br>Update: (format: yyyy-mm-dd): 
			<input name=\"start_date\" type=\"text\"></p>\n
		<p>End Date: $end_date <br>Update: (format: yyyy-mm-dd): 
			<input name=\"end_date\" type=\"text\"></p>\n";
if ($_SESSION["user_type"] == "tutor"){
	echo "<p>Min Rate Per Hour (What is the least you're willing to accept?): $rate <br>
		Update: <input name=\"min_rate\" type=\"number\" min=\"10\" max=\"30\"></p>";
} else if ($_SESSION["user_type"] == "student"){
	echo "<p>Max Rate Per Hour (What is the most you're willing to pay?): $rate <br>
		Update: <input name=\"max_rate\" type=\"number\" min=\"10\" max=\"30\"></p>";
}
echo "
		<p>Your First Language (mother tongue): $first_lang <br> \n
			Update: <input name=\"first_lang\" type=\"text\"></p>\n
		<p>Your Second Language: $second_lang <br> \n
			Update: <input name=\"second_lang\" type=\"text\"></p>\n
		
		<input type=\"hidden\" name=\"mode\" value=\"update_user\"></input>\n
		
		<button class=\"button\">Update User</button>\n
		<p id=\"errors\" style=\"color:red;\"></p>\n
		
	</form>\n
	";


?>

	</div>

	
	
	
<!-- FILTER TUTORS -->
<?php if ($_SESSION["user_type"] == "student"): ?>
<h2>Filter Tutors</h2>
	<!-- Form submitted via ajax method create_user() in index.js: -->
	<form id="filter_tutor" action="filter_tutor.php" method="post"> 
		<p>Tutors born on or after year: <input name="year_born" type="number" min="1920" max="2010"></p>
		<p>Gender: 
			<select name="gender">
				<option value="m">Male</option>
				<option value="f">Female</option>
			</select>
		</p>
		<p>Start Date (format: yyyy-mm-dd): <input name="start_date" type="text"></p>
		<p>End Date (format: yyyy-mm-dd): <input name="end_date" type="text"></p>
		<p>Min Rate Per Hour: <input name="min_rate" type="number" min="10" max="30"></p>
		<p>Tutor's First Language: <input name="first_lang" type="text"></p>
		<p>Tutor's Second Language: <input name="second_lang" type="text"></p>
		<p>Filter Tutors: <input type="submit" class="button" /></p>
	</form>
<?php endif; ?>	
	
	
	
<!-- DELETE CURRENT USER'S ACCOUNT -->
	<div class="box container">
		<button onclick="window.location.replace('delete_acct.php');" class="button">Delete My Account</button>
	</div>
	

	
	
<?php
	/*Functions to query database, used at the top of this page.*/
	
	/*Pre-conditions: post variables must be set 
		and function must be called only if $_POST['mode'] = 'update_user' */
	function update_user(){
	
		//use $_POST variables to insert into database...
		$keys = ['fname', 'lname', 'year_born','gender','skype_id','start_date','end_date','min_rate','first_lang','second_lang'];
		$pairs = array();
		foreach ($keys as $name) {
			if (isset($_POST[$name]) && !($_POST[$name] == ""))
				$pairs[$name] = $_POST[$name];
		}

		update_table($pairs, $_SESSION["user_type"]);
		
		return;
	}
	
	//the following two functions modified from instructor's response on 5/10/15
	// to question posed on 5/9/15 on piazza.com
	// regarding variable column updates

	function update_table($arr, $table_name) {

		include("db.php");
		
		$params = array();
		$fragments = array();
		foreach ($arr as $col => $val) {
			$fragments[] = "{$col} = ?";
			$params[] = $val;
		}

		$sql = sprintf("UPDATE %s SET %s %s", $table_name, implode(", ", $fragments), " WHERE user_name = ?");
		
		if (!($stmt = $mysqli->prepare($sql))) {
			echo "Prepare failed" . $stmt->errno . " " . $stmt->error;
		} else {
			$params = array_merge(array(str_repeat('s', count($params) + 1)), array_values($params), array($_SESSION["user"]));
			
			call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));

			$stmt->execute();
			$stmt->close();
		}
	}

	function refValues($arr){
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
	
	
?>

	
</body>

</html>
