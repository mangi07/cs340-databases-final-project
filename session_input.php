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
	!($_SESSION["user_type"] == "tutor")){
	echo "<div class='box'>You must be logged in as a TUTOR to view this page.<br>
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
	
	<script type="text/javascript">
		function submitSession(){
			//clear any previous error messages there may have been
			$("#errors").text("");
			
			var start = $("#start").val(); //eg: "2015-02-02T01:00"
			var end = $("#end").val();
			if(checkDates(start, end)){
				submitDates(start, end);
			};
		}
		//check if start is earlier than end date
		//	and notify user of the time difference
		function checkDates(start, end){
			if(start == null || start == "" ||
				end == null || end == ""){
				$("#errors").text("You must enter both dates!");
				return false;
			}else{
				start = new Date(start);
				end = new Date(end);
			}
			if(start < end){
				var diff = (end - start)/3600000;
				$("#diff").text("Time Difference: " + diff + " hour(s)");
				return true;
			}else{
				$("#errors").text("Start date must be earlier than end date!");
				return false;
			}
			return true;
		}
		function submitDates(start, end){
			//format for insertion to database
			start = start.replace("T", " ") + ":00";
			end = end.replace("T", " ") + ":00";
			var id = $("#student_id").val();
			
			//post to php that inserts to database and echoes back message to check here
			$.post( "session_record.php", { start:start, end:end, id:id })
				.done(function( data ) {
					$("#response").html(data);
				})
				.fail(function() {
					$('#errors').text("Failed to communicate with the server.");
				});
			return;
		}
	</script>
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

<?php if($_SESSION["user_type"]=="tutor"): ?>
	<h3 id="debug">RECORD SESSION</h3>
	<form method="post" action="log_session.php">
		<!-- NEED TO ADD WAY TO SELECT ONE OF THE TUTOR'S STUDENTS AND POST THAT, TOO! -->
		<select name="student" id="student_id">
<?php
	include("db.php");
	if(!($stmt = $mysqli->prepare("
		select s.fname, s.lname, s.id from student as s inner join
		student_tutor as st
		on s.id = st.sid inner join
		tutor as t
		on t.id = st.tid
		where t.user_name = ?
		order by lname, fname
	"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("s",$_SESSION['user']))){
		echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->bind_result($fname, $lname, $id)){
		echo "Bind paramaters failed: " . $stmt->errno . " " . $stmt->error;
	};
	$student = "";
	while($stmt->fetch()){
		$student .= $fname . " " . $lname . ", id: " . $id;
		echo "<option value='$id'>$student</option>";
		$student = "";
	}
?>
		</select> 
		<p>Start Time: <input type="datetime-local" name="start_time" id="start"></p>
		<p>End Time: <input type="datetime-local" name="end_time" id="end"></p>
		<p id="diff">Time Difference: </p>
		<p class="errors" id="errors"></p>
	</form>
	<button onclick="submitSession();" class="button">Submit Session</button>
	<p id="response"></p>
<?php endif; ?>
	
	<div class="button"><a href="main.php">Back To Main</a></div>
	
	
</body>

</html>