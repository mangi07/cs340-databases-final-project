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

<?php
if (isset($_POST["user_type"]) && ($_POST["user_type"] == "student" || $_POST["user_type"] == "tutor")):
?>

<?php if ($_POST["user_type"] == "tutor"): ?>
	<h1>CREATE NEW TUTOR ACCOUNT:</h1>
<?php elseif ($_POST["user_type"] == "student"):?>
	<h1>CREATE NEW STUDENT ACCOUNT:</h1>
<?php endif; ?>

	<!-- Form submitted via ajax method create_user() in index.js: -->
	<form id="new_user_form" action="/"> 
		<p>Username: <input name="username" type="text"></p>
		<p>Password: <input name="password" type="password"></p>
		
		<p>First Name: <input name="fname" type="text"></p>
		<p>Last Name: <input name="lname" type="text"></p>
		<p>Year Born: <input name="year_born" type="number" min="1920" max="2010"></p>
		<p>Gender: 
			<select name="gender">
				<option value="m">Male</option>
				<option value="f">Female</option>
			</select>
		</p>
		<p>Skype ID: <input name="skype_id" type="text"></p>
		<p>Start Date (format: yyyy-mm-dd): <input name="start_date" type="text"></p>
		<p>End Date (format: yyyy-mm-dd): <input name="end_date" type="text"></p>
<?php if ($_POST["user_type"] == "tutor"): ?>
		<p>Min Rate Per Hour (What is the least you're willing to accept?): <input name="min_rate" type="number" min="10" max="30"></p>
<?php elseif ($_POST["user_type"] == "student"):?>
		<p>Max Rate Per Hour (What is the most you're willing to pay?): <input name="max_rate" type="number" min="10" max="30"></p>
<?php endif; ?>
		<p>Your First Language (mother tongue): <input name="first_lang" type="text"></p>
		<p>Your Second Language: <input name="second_lang" type="text"></p>
		
		<input type="hidden" name="login_attempt" value="false"></input>
		
		<!-- ajax post the user type with the other post variables above, to accounts.php 
		using JavaScript create_user() from index.js -->
<?php if ($_POST["user_type"] == "tutor"): ?>
		<input type="hidden" name="user_type" value="tutor"></input>
<?php elseif ($_POST["user_type"] == "student"):?>
		<input type="hidden" name="user_type" value="student"></input>
<?php endif; ?>
		<button id="create_user" class="button">Create User and Login</button>
		<p id="errors" style="color:red;"></p>
		
	</form>
	
<?php endif; ?>
	

</body>

</html>