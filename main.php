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
note: insert into availability all 0's when creating a new user
note: add manager page to view how many students each tutor has, sessions, and financial info
-->


<?php
	/*refer to functions at the bottom of this page*/
	if (isset($_POST['mode']) && $_POST['mode'] = 'update_user' ){
		//update user function
		update_user();
	}
	//similar pattern for mode checking and function calls follow...
	
	
?>







<!-- VIEW PERSONAL DATA -->
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
