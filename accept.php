

EDIT THIS TO ACCEPT $_POST['<STUDENT_ID>'] AND INSERT IT INTO student_tutor



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
	//insert the relationship between student and tutor
	if(isset($_POST['student_id'])){
		include("db.php");
		if(!($stmt = $mysqli->prepare("
			insert into cs340final_project.student_tutor(sid, tid, rate, start_date) 
			values (?, (select cs340final_project.id from tutor where user_name = ?), ?, ?)"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("iiis",$_POST['student_id'],$_SESSION['user']))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "<p class='errors'>Error: The relationship with this tutor may already exist.</p>";
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		} else {
			echo "<p>Request for tutor submitted!</p>";
		}
		$stmt->close();
	} else {
		echo "<p class='errors'>Error: Could not get this tutor's id to submit request.</p>";
	};
	
	
	/*
-- example of adding a connection between a student and a tutor in student_tutor table:
insert into student_tutor(sid, tid, rate, start_date) values (
(select id from student where user_name = 'studentUser1'),
(select id from tutor where user_name = 'tutorUser1'),
15,
'2015-01-15'
);
-- upon establishing this relationship between a student and tutor, delete the
--   corresponding relationship in student_wants_tutor 
--   (done this way because it's without knowing how to use triggers)
delete from student_wants_tutor where (sid, tid) = (
(select id from student where user_name = 'studentUser1_changed'),
(select id from tutor where user_name = 'tutorUser1')
);
	*/
	
?>

	
	<div class="button"><a href="main.php">Return To Main Page</a></div>


	
	
</body>

</html>