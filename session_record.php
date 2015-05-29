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
	echo "<div class='box'>You must be logged in as a TUTOR to view this page.<br>
		<button onclick='window.location.href = \"index.php\"' class='button'>Log In</button></div>";
	die();
}


if(isset($_POST["start"]) && isset($_POST["end"])){
	include("db.php");
	if(!($stmt = $mysqli->prepare("
	EDIT THIS!!!
		insert into sessions(sid, tid, start_time, end_time, rate)
values (
  (select id from student where user_name = 'studentUser1_changed'),
  (select id from tutor where user_name = 'tutorUser1'),
  '2015-05-17 21:42:45', now(),
( select rate from student_tutor where (sid, tid) = (
  (select id from student where user_name = 'studentUser1_changed'),
  (select id from tutor where user_name = 'tutorUser1')
) )
);
	"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("s",$_SESSION['user']))){
		echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
	}
 . " and " . $_POST["end"];
	
	
?>
