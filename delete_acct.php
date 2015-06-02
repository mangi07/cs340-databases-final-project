<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 9, 2015
COURSE: CS 340 - Introduction to Databases, Oregon State University
*/

//connect to the database
include ("db.php");

$user;
$user_type;
$stmt;

if (isset($_SESSION["user"]) && isset($_SESSION["user_type"])){//session variable that identifies the type of user: 'tutor', 'student', or 'manager'
//and prepare statement based on each

	$user = $_SESSION["user"];

	if(!($stmt = $mysqli->prepare("DELETE FROM users2 WHERE user_name = ? limit 1"))){
		echo "<p class='box'>Failed to prepare for executing delete operation.<p>";
		echo "<a href='main.php' class='button'>Back To Main Page</a>";
		die();
	}

	
	if (!(
		$stmt->bind_param("s", $user) &&
		$stmt->execute()
	)) {
		echo "<p class='box'>Failed to execute the delete operation.<p>";
		echo "<a href='main.php' class='button'>Back To Main Page</a>";
		
		$stmt->close();
		$mysqli->close();
		die();
	}
	$stmt->close();
	$mysqli->close();
	
	
	$_SESSION = array();
	session_destroy();
	$filePath = explode('/', $_SERVER['PHP_SELF'], -1);
	$filePath = implode('/', $filePath);
	$redirect = "http://" . $_SERVER['HTTP_HOST'] . $filePath;
	header("Location: {$redirect}/index.php", true);
	echo "<script>window.location.replace('index.php');</script>";
	die();
	
}


?>
