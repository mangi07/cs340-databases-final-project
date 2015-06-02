<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 23, 2015
COURSE: CS 340 - Web Development, Oregon State University
*/


//check for login as student only
if (!isset($_SESSION['user']) && 
	!isset($_SESSION["user_type"])){
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

	<!-- <div onclick="window.location.href = 'logout.php'" class="button">Log Out</div> -->
	<div class="button"><a href="logout.php">Log Out</a></div>


<?php	
	//student deletes relationship with a tutor
	if($_SESSION["user_type"]=="student" && 
		isset($_POST["full_name"]) &&
		isset($_POST["tutor_id"])){
		
		include("db.php");
		
		if(!($stmt = $mysqli->prepare("
			delete from student_tutor
			where (sid,tid)=((select id from student where user_name = ?), ?)
			limit 1
		"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("si",$_SESSION['user'],$_POST['tutor_id']))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "<p class='errors'>Error: Unable to delete this tutor.</p>";
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		} else {
			echo "<p>You have successfully deleted your relationship with tutor, " . $_POST["full_name"] . ".";
		}
		$stmt->close();
		
	};
	
	//tutor deletes relationship with a student
	if($_SESSION["user_type"]=="tutor" && 
		isset($_POST["full_name"]) &&
		isset($_POST["student_id"])){
		
		include("db.php");
		
		if(!($stmt = $mysqli->prepare("
			delete from student_tutor
			where (sid,tid)=(?,(select id from tutor where user_name = ?))
			limit 1
		"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("is",$_POST['student_id'],$_SESSION['user']))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "<p class='errors'>Error: Unable to delete this student.</p>";
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		} else {
			echo "<p>You have successfully deleted your relationship with student, " . $_POST["full_name"];
		}
		$stmt->close();
		
	};
	
	
?>

	
	<div class="button"><a href="main.php">Return To Main Page</a></div>


	
	
</body>

</html>