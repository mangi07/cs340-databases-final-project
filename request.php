<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 23, 2015
COURSE: CS 340 - Introduction to Databases, Oregon State University
*/


//check for login as student only
if (!isset($_SESSION['user']) && 
	!isset($_SESSION["user_type"]) && 
	!($_SESSION["user_type"] == "student")){
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

	<!-- <div onclick="window.location.href = 'logout.php'" class="button">Log Out</div> -->
	<div class="button"><a href="logout.php">Log Out</a></div>


<?php	
	//insert the request
	if(isset($_POST['tutor_id'])){
		include("db.php");
		
		if(!($stmt = $mysqli->prepare("
			select if(max_rate > min_rate, (min_rate+max_rate)/2, min_rate)
			from
			(select min_rate from tutor where id = ? limit 1) as t
			inner join
			(select max_rate from student where user_name = ? limit 1) as s
		"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("is",$_POST['tutor_id'],$_SESSION['user']))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "<p class='errors'>Error: Unable to calculate a rate between student and tutor.</p>";
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}
		$stmt->bind_result($rate);
		$stmt->fetch();
		$stmt->close();
		//debug
		//var_dump($rate); //string "15.0000"
		
		if(!($stmt = $mysqli->prepare("INSERT INTO student_wants_tutor(sid, tid, rate) values ((select id from student where user_name = ?), ?, ?)"))){
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("sis",$_SESSION['user'],$_POST['tutor_id'],$rate))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!$stmt->execute()){
			echo "<p class='errors'>Error: The relationship with this tutor may already exist.</p>";
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		} else {
			echo "<p>Request for tutor submitted!</p>";
			//notify student of what the pay rate will be and how it is calculated (explanation follows):
			echo "<p>You have requested this tutor at a pay rate of $rate per hour.</p>\n
				<p>This is the average between tutor's min and your max, or the tutor's min if higher than your max.</p>
				<p>If and when this tutor accepts your request, you will see the new tutor listed on the main page.</p>
			";
			
		}
		$stmt->close();
	} else {
		echo "<p class='errors'>Error: Could not get this tutor's id to submit request.</p>";
	};
	
	
	
	
?>

	
	<div class="button"><a href="main.php">Return To Main Page</a></div>


	
	
</body>

</html>