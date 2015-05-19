<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	March 8, 2015
COURSE: CS 290 - Web Development, Oregon State University
*/


//check if user is already logged in
if(isset($_SESSION['user_name'])){
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
}


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
	
	return $checks_passed;
}

//if the username exists and the password is correct,
//  this will allow user to access main.php
function db_login($user, $pass, $mysqli){
	if (!($stmt = $mysqli->prepare("SELECT password FROM cs340final_project.users WHERE user_name = ?"))) {
		echo "Error: Failed to check the database for this user.<br>";
		return;}
	if (!$stmt->bind_param("s", $user)) {
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
	
	
/*
	-- inserting into student table example:
--   First try inserting username/password pair into users table:
insert into users(user_name, password) values ('studentUser1', 'studentPassword1');
-- If that returns success, then...
insert into student(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, max_rate, first_lang, second_lang) values (
'studentUser1', 'John', 'Doe', 1980, 'm', 'jd', '2015-01-01', '2015-02-01', 20, 'English', 'Korean');
-- Else let the user know the username and password could not be entered (maybe username was not unique).

-- inserting into tutor table example:
--   First try inserting username/password pair into users table:
insert into users(user_name, password) values ('tutorUser1', 'tutorPassword1');
-- If that returns success, then...
insert into tutor(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, min_rate, first_lang, second_lang) values (
'tutorUser1', 'Susie', 'Q', 1970, 'f', 'sq', '2015-01-01', '2015-02-01', 10, 'Korean', 'English');
-- Else let the user know the username and password could not be entered (maybe username was not unique).
*/

	if (!($stmt = $mysqli->prepare("insert into cs340final_project.users(user_name, password) values (?, ?);"))) {
		echo "Error: This user may already exist.<br>";	
		return;
	} else if (!$stmt->bind_param("ss", $user, $pass)) {
		echo "Failed to add this user to the database.<br>";	
		return;
	} else if (!$stmt->execute()) {
		echo "Failed to add this user to the database.  The user may already exist.<br>";
		return;
	} else if ($user_type == "student") {
		if (!($stmt = $mysqli->prepare("insert into cs340final_project.student(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, max_rate, first_lang, second_lang) values (?,?,?,?,?,?,?,?,?,?,?);"))) {
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
		if (!($stmt = $mysqli->prepare("insert into cs340final_project.tutor(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, min_rate, first_lang, second_lang) values (?,?,?,?,?,?,?,?,?,?,?);"))) {
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


