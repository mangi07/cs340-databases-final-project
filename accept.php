<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 23, 2015
COURSE: CS 340 - Web Development, Oregon State University
*/


//check for login as student only
if (!isset($_SESSION['user']) && 
	!isset($_SESSION["user_type"]) && 
	!($_SESSION["user_type"] == "tutor")){
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
	if(isset($_POST['student_id'])){
		include("db.php");
		if(!($stmt = $mysqli->prepare("
			insert into student_tutor(sid, tid, rate, start_date) values (?, (select id from tutor where user_name = ? limit 1), (select rate from student_wants_tutor where (sid,tid)=(?, (select id from tutor where user_name = ? limit 1)) limit 1), now())"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("isis",$_POST['student_id'],$_SESSION['user'],$_POST['student_id'],$_SESSION['user']))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "<p class='errors'>Error: The relationship with this tutor may already exist.</p>";
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		} else {
			echo "<p>You now have a new student!  Return to the main page to see your students.</p>";
			//upon establishing this relationship between a student and tutor, delete the
			//corresponding relationship in student_wants_tutor
			delete_swt_relation();
		}
		$stmt->close();
	} else {
		echo "<p class='errors'>Error: Could not get this tutor's id to submit request.</p>";
	};
	//helper function to delete relationship in student_wants_tutor
	function delete_swt_relation(){
		global $mysqli;
		if(!($stmt = $mysqli->prepare("
			delete from student_wants_tutor where (sid, tid) = (?,(select id from tutor where user_name = ?))"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("is",$_POST['student_id'],$_SESSION['user']))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "<p class='errors'>Error: The relationship with this tutor may already exist.</p>";
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}
		$stmt->close();
	}
		
?>

	
	<div class="button"><a href="main.php">Return To Main Page</a></div>


	
	
</body>

</html>