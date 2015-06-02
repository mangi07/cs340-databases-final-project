<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 31, 2015
COURSE: CS 340 - Introduction to Databases, Oregon State University
*/



//check if user is already logged in
if(isset($_SESSION['user'])){
	echo "Note: You are already logged in.<br>";
	echo "<button onclick='window.location.href = \"main.php\"'>User Page</button>";
	die();
}

if(isset($_POST["username"]) && 
	isset($_POST["password"]) &&
	isset($_POST["login_attempt"]) &&
	isset($_POST["user_type"])){
	
	$username = $_POST["username"];
	$password = $_POST["password"];
	$login_attempt = $_POST["login_attempt"];
	$user_type = $_POST["user_type"]; //This could be "student", "tutor", or "manager"
	
	//check that all fields have been filled in:
	if(!check_fields($username, $password)) die();
	
	//check username and password against the database
	require("db.php");	//connect to the database
						//  and create $mysqli object for use in this file
	if ($login_attempt == 'true'){
		//wraps function calls to the database
		db_login($username, $password, $mysqli);
	} else {
		//wraps function calls to the database
		create_user($username, $password, $mysqli, $user_type);
	}
	$mysqli->close();
	
} else {
	echo "<span style='color:red;'>
		Sorry...Unknown error from php!
		</span>";
	die();
}

/*  check these fields: in check_fields()
$_POST["username"],
$_POST["password"],
	$_POST["fname"],
	$_POST["lname"],
	$_POST["year_born"],
	$_POST["gender"],
	$_POST["skype_id"],
	$_POST["start_date"],
	$_POST["end_date"],
	$_POST["max_rate"],
	$_POST["first_lang"],
	$_POST["second_lang"]
*/

//returns true if username and password pass the checks, else returns false
function check_fields($user, $pass){
	$checks_passed = true;

	if ($user == ""){
		echo "Error: Username is required.<br>";
		$checks_passed = false;
	}
	if ($pass == ""){
		echo "Error: Password is required.<br>";
		$checks_passed = false;
	}
	if (preg_match('/\s/',$user)){
		echo "Error: Username cannot contain any spaces.<br>";
		$checks_passed = false;
	}
	if (preg_match('/\s/',$pass)){
		echo "Error: Password cannot contain any spaces.<br>";
		$checks_passed = false;
	}
	//if it's create account, then check the additional post variables
	//  using same kind of logic
	if ($_POST["login_attempt"] == "false"){
		if ($_POST["fname"]=="") {echo "Error: First Name is required.<br>"; $checks_passed = false;}
		if ($_POST["lname"]=="") {echo "Error: Last Name is required.<br>"; $checks_passed = false;}
		if ($_POST["year_born"]=="") {echo "Error: Year Born is required.<br>"; $checks_passed = false;}
		if ($_POST["gender"]=="") {echo "Error: Gender is required.<br>"; $checks_passed = false;}
		if ($_POST["skype_id"]=="") {echo "Error: Skype ID is required.<br>"; $checks_passed = false;}
		if ($_POST["start_date"]=="") {echo "Error: Start Date is required.<br>"; $checks_passed = false;}
		if ($_POST["end_date"]=="") {echo "Error: End Date is required.<br>"; $checks_passed = false;}
		if ($_POST["user_type"]=="student") {
			if ($_POST["max_rate"]=="") {echo "Error: Max Rate is required.<br>"; $checks_passed = false;}
		} else if ($_POST["user_type"]=="tutor") {
			if ($_POST["min_rate"]=="") {echo "Error: Min Rate is required.<br>"; $checks_passed = false;}
		}
		if ($_POST["first_lang"]=="") {echo "Error: First Language is required.<br>"; $checks_passed = false;}
		if ($_POST["second_lang"]=="") {echo "Error: Second Language is required.<br>"; $checks_passed = false;}
	}
	//additional error checking on some fields using regular expressions...add it
	
	
	return $checks_passed;
}

//if the username exists and the password is correct,
//  this will allow user to access main.php
function db_login($user, $pass, $mysqli){
	//MODIFY THIS SELECT TO MAKE SURE IT'S THE CORRECT TYPE OF user
	//  so a user doesn't accidentally fill out the wrong section
	//  and get sent to the wrong type of user interface.
	//  Then, check the create user stuff - it actually worked - why?
	
	//if student
	if ($_POST["user_type"] == "student") {
		if (!($stmt = $mysqli->prepare("SELECT password FROM users2 as u, student as s WHERE u.user_name = ? and s.user_name = ?"))) {
			echo "Error: Failed to check the database for this user.<br>";
			return;
		}
	}
	//if tutor, mirrored/similar code...
	if ($_POST["user_type"] == "tutor") {
		if (!($stmt = $mysqli->prepare("SELECT password FROM users2 as u, tutor as t WHERE u.user_name = ? and t.user_name = ?"))) {
			echo "Error: Failed to check the database for this user.<br>";
			return;
		}
	}
	
	if (!$stmt->bind_param("ss", $user, $user)) {
		echo "Failed to check the database for this user.<br>";
		return;}
	if (!$stmt->execute()) {
		echo "Failed to check the database for this user.<br>";
		return;}
	$db_pass = NULL;
	if (!$stmt->bind_result($db_pass)) {
		echo "Failed to check the database for this user's password.<br>";
		return;}
	if(!$stmt->fetch()){
		echo "This username does not exist.<br>";
		return;
	}
	
	//check password and create session variable if password is correct
	if ($pass == $db_pass){
		echo "success";
		//create session variable to indicate successful login
		if(session_status() == PHP_SESSION_ACTIVE){
			$_SESSION["user"] = $user;
			$_SESSION["user_type"] = $_POST["user_type"];
		} else {
			echo "Error: unknown!<br>";
		}
	} else {
		echo "Wrong password!<br>";
		return;
	}
}


//if the username already exists, this should fail,
//  else a new username and password will be entered as a row in the db table,
//  and then the new user will have access to main.php through a session variable
function create_user($user, $pass, $mysqli, $user_type){

	if (!($stmt = $mysqli->prepare("insert into users2(user_name, password) values (?, ?);"))) {
		echo "Error: Failed to prepare to add user.<br>";	
		return;
	} else if (!$stmt->bind_param("ss", $user, $pass)) {
		echo "Error: Failed to prepare to add user.<br>";	
		return;
	} else if (!$stmt->execute()) {
		echo "Error: Failed to add this user to the database.  The user may already exist.<br>";
		return;
	} else if ($user_type == "student") {
		if (!($stmt = $mysqli->prepare("insert into student(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, max_rate, first_lang, second_lang) values (?,?,?,?,?,?,?,?,?,?,?);"))) {
		//eg: 'studentUser1', 'John', 'Doe', 1980, 'm', 'jd', '2015-01-01', '2015-02-01', 20, 'English', 'Korean'
			echo "Error: This user may already exist.<br>";	
			return;
		}
		if (!$stmt->bind_param("ssssssssiss", 
								$_POST["username"],
								$_POST["fname"],
								$_POST["lname"],
								$_POST["year_born"],
								$_POST["gender"],
								$_POST["skype_id"],
								$_POST["start_date"],
								$_POST["end_date"],
								$_POST["max_rate"],
								$_POST["first_lang"],
								$_POST["second_lang"]
								)) {
			echo "Failed to add this user to the database.<br>";	
			return;
		}
		if (!$stmt->execute()) {
			echo "Failed to add this user to the database.  The user may already exist.<br>";
			return;
		}
	} else if ($user_type == "tutor") {
		if (!($stmt = $mysqli->prepare("insert into tutor(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, min_rate, first_lang, second_lang) values (?,?,?,?,?,?,?,?,?,?,?);"))) {
		//eg: 'studentUser1', 'John', 'Doe', 1980, 'm', 'jd', '2015-01-01', '2015-02-01', 20, 'English', 'Korean'
			echo "Error: This user may already exist.<br>";	
			return;
		}
		if (!$stmt->bind_param("ssssssssiss", 
								$_POST["username"],
								$_POST["fname"],
								$_POST["lname"],
								$_POST["year_born"],
								$_POST["gender"],
								$_POST["skype_id"],
								$_POST["start_date"],
								$_POST["end_date"],
								$_POST["min_rate"],
								$_POST["first_lang"],
								$_POST["second_lang"]
								)) {
			echo "Failed to add this user to the database.<br>";	
			return;
		}
		if (!$stmt->execute()) {
			echo "Failed to add this user to the database.  The user may already exist.<br>";
			return;
		}
	}
	
	
	//create session variable to indicate successful login
	if(session_status() == PHP_SESSION_ACTIVE){
		$_SESSION["user"] = $user;
		$_SESSION["user_type"] = $user_type;
		echo "success";
	} else {
		echo "Error: unknown!<br>";
	}
	
}

?>


