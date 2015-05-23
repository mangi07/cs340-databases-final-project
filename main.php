<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 23, 2015
COURSE: CS 340 - Web Development, Oregon State University
*/


//check for login
if (!isset($_SESSION['user'])){
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
	
	<!-- To modifiy and view data: -->
	<div class="horizontal">
	
		
		
		<!-- To enter the coordinates of a new location entry: -->
		<div class="box">
			
		</div>
		
		
		<div class="box">
			
			
		</div>
		
	</div>
	
	<!-- To filter and see other user's locations -->
	<div class="box container">
		
	</div>
	
	<!-- To delete the current user's account -->
	<div class="box container">
		<button onclick="window.location.replace('delete_acct.php');" class="button">Delete My Account</button>
	</div>
	
	</div>
	
</body>

</html>
