<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	March 8, 2015
COURSE: CS 290 - Web Development, Oregon State University
*/

//check for login
if (isset($_SESSION['user'])){
	echo "Note: You are already logged in.<br>";
	echo "<button onclick='window.location.href = \"main.php\"'>User Page</button>";
	die();
}


?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>CS 340 Final Project - Ben R. Olson</title>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script type="text/javascript" src="jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="index.js"></script>
</head>

<body>
	<div class="centered box">
		<h1>ESL Tutoring Portal</h1>
		<h3>Created By Ben R. Olson</h3>

		<h2>STUDENTS LOGIN HERE:</h2>
		<p>Login or Create an Account</p>
		<p>Username: <input id="student_userfield" type="text"></p>
		<p>Password: <input id="student_passfield" type="password"></p>
		<button id="student_login" class="button">Login</button>
		
		<!-- FORM: CREATE STUDENT: ACTUALL, PUT THIS STRAIGHT IN main.php? -->
		<h2>CREATE NEW STUDENT ACCOUNT HERE:</h2>
		<form method="post" action="accounts.php">
			<fieldset>
			<legend>New Student Information</legend>
				<p>Planet Name: <input type="text" name="PName" /></p>
				<p>Planet Population: <input type="text" name="PPopulation" /></p>
				<p>Official Language: <input type="text" name="PLanguage" /></p>
				<input type="hidden" name="create_user" value="true" />
			</fieldset>
			<input type="submit" name="createStudent" value="Create Student User" class="button"/>
		</form>
		
		
		<button id="create_student" class="button">Create Student User</button>
		<p id="student_errors" style="color:red;"></p>
		
		<h2>TUTORS HERE:</h2>
		<p>Login or Create an Account</p>
		<p>Username: <input id="tutor_userfield" type="text"></p>
		<p>Password: <input id="tutor_passfield" type="password"></p>
		<button id="tutor_login" class="button">Login</button>
		<!-- add stuff here for form submission of all post variables
		required to insert a new user into database: see account.php line 176 onward-->
		<button id="create_tutor" class="button">Create Tutor User</button>
		<p id="tutor_errors" style="color:red;"></p>
	</div>
</body>


