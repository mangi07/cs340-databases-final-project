<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	March 8, 2015
COURSE: CS 290 - Web Development, Oregon State University
*/


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

		<h2>STUDENTS HERE:</h2>
		<p>Login or Create an Account</p>
		<p>Username: <input id="student_userfield" type="text"></p>
		<p>Password: <input id="student_passfield" type="password"></p>
		<button id="student_login" class="button">Login</button>
		<!-- add stuff here for form submission of all post variables
		required to insert a new user into database: see account.php line 150 onward-->
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


