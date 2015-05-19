<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	March 8, 2015
COURSE: CS 290 - Web Development, Oregon State University
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
	
	<!--link to main JavaScript-->
	<script type="text/javascript" src="main.js"></script>
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
	
	<!-- To modifiy and view data: -->
	<div class="horizontal">
	
	<!-- To enter all data for a new location to be placed on the user's map -->
	<div id="newEntry" class="container">
		<h2 title="Add a location if none exist.">Add Or Update Your Location</h2>
		
		<h3 class="box">Location Name: <input id="loc_name" type="text"></h3>
		
		<!-- To choose the range of days the user was at a location: -->
		<div id="dateContainer" class="box">
			<h3 title="When were you there?">Timeframe</h3>
			<div id="date">
				<div class="horizontal">
					<h3>Start Date</h3>
					<div id="startDate"></div>
				</div>
				
				<div class="horizontal">
					<h3>End Date</h3>
					<div id="endDate"></div>
				</div>
			</div>
		</div>
		
		<!-- To enter the coordinates of a new location entry: -->
		<div class="box">
			<h3 title="Click on the map to get your coordinates.">Location</h3>
			<p id="lat">Latitude: not yet selected.</p>
			<p id="lng">Longitude: not yet selected.</p>
			
			<!-- using $.post() -->
			<button class="ajax button">Submit</button>
			<div id="newEntryErrors"></div>
		</div>
		
		<!-- change this to php that gets session variable to reflect whether user is visible (cf accounts.php line 158 -->
		<div class="box">
			
			<?php if (isset($_SESSION['visible'])  && $_SESSION['visible'] == 1): ?>
				<p id="privacy_notice">Your location is visible to other users.</p>
				<button id="visibility" value="visible" class="button">Hide My Location</button>
			<?php else: ?>
				<p id="privacy_notice">Your location is hidden.</p>
				<button id="visibility" value="hidden" class="button">Show My Location</button>
			<?php endif; ?>
			
			<p id="vis_errors" style="color:red;"><p>
		</div>
		
	</div>
	
	<!-- To filter and see other user's locations -->
	<div id="viewOthers" class="box container">
		<h3 title="You can see the location of others when you select them from the list below.">View Other Users</h3>
		<p>Other users available to display:</p>
		
		<button class="allUsers button">Get All Users</button>
		
		<!-- Checkboxes go here -->
		<div id="user_checkboxes"></div>
		
		<div id="allUsersErrors"></div>
	</div>
	
	<!-- To delete the current user's account -->
	<div class="box container">
		<button onclick="window.location.replace('delete_acct.php');" class="button">Delete My Account</button>
	</div>
	
	</div>
	
</body>

</html>
