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
				alert(diff);
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
			//alert(start);
			//alert(end);
			
			//post to php that inserts to database and echoes back message to check here
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
		<p>Start Time: <input type="datetime-local" name="start_time" id="start"></p>
		<p>End Time: <input type="datetime-local" name="end_time" id="end"></p>
		<p id="diff">Time Difference: </p>
		<p class="errors" id="errors"></p>
	</form>
	<button onclick="submitSession();">Debug Check Dates</button>
<?php endif; ?>
	
	
	
</body>

</html>