<?php
session_start();

/*
AUTHOR:	Benjamin R. Olson
DATE:	May 23, 2015
COURSE: CS 340 - Web Development, Oregon State University
*/


//check for login
if (!isset($_SESSION['user']) && !isset($_SESSION["user_type"])){
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

<body>


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
	<!--<div class="button"><a href="logout.php">Log Out</a></div>-->
	
	<?php 
		//get tutors
		$tutors = filter_tutor();
	?>
	
	<!-- display tutors to user -->
	<p>AVAILABLE TUTORS</p>
	<table>
		<tr>
			<th colspan='9'>Tutors</th>
		</tr>
		<tr>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Year Born</th>
			<th>Gender</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Minimum Rate</th>
			<th>First Language</th>
			<th>Second Language</th>
		</tr>
<?php
	foreach ($tutors as $key => $val) {
		echo "<tr>\n";
		
		//counter logic from http://stackoverflow.com/questions/1070244/how-to-determine-the-first-and-last-iteration-in-a-foreach-loop
		$i = 0;
		$len = count($val);
		foreach ($val as $k => $v) {
			if ($i < $len-1) {
				echo "<td>$v</td>";
			}
			$i++;
		}
	}
?>
	</table>
	
	<p>REQUEST TUTOR HERE</p>
	<form method="post" action="request.php">
		<select name="tutor_id">
		<?php
			foreach ($tutors as $key => $val){
				$tutor_id = $val['id'];
				$tutor_name = $val['fname'] . " " . $val['lname'];
				echo "<option value='$tutor_id'>$tutor_name</option>\n";
			}
		?>
		</select>
		<input type='submit'></input>
	</form>
			

<?php
	
	//the following two functions modified from instructor's response on 5/10/15
	// to question posed on 5/9/15 on piazza.com
	// regarding variable column updates

	function filter_tutor() {

		include("db.php");
		
		//use $_POST variables to select from database...
		$keys = ['year_born','gender','start_date','end_date','min_rate','first_lang','second_lang'];
		
		//set up the string that follows the WHERE clause
		$conditions = array();
		$params = array();
		$cond_str = "";
		
		if (isset($_POST['year_born']) && !($_POST['year_born'] == "")) {
			$conditions[] = "year_born <= ?";
			$params[] = $_POST['year_born'];
		}
		if (isset($_POST['gender']) && !($_POST['gender'] == "")) {
			$conditions[] = "gender = ?";
			$params[] = $_POST['gender'];
		}
		if (isset($_POST['start_date']) && !($_POST['start_date'] == "")) {
			$conditions[] = "start_date >= ?";
			$params[] = $_POST['start_date'];
		}
		if (isset($_POST['end_date']) && !($_POST['end_date'] == "")) {
			$conditions[] = "end_date <= ?";
			$params[] = $_POST['end_date'];
		}
		if (isset($_POST['min_rate']) && !($_POST['min_rate'] == "")) {
			$conditions[] = "min_rate >= ?";
			$params[] = $_POST['min_rate'];
		}
		if (isset($_POST['first_lang']) && !($_POST['first_lang'] == "")) {
			$conditions[] = "first_lang = ?";
			$params[] = $_POST['first_lang'];
		}
		if (isset($_POST['second_lang']) && !($_POST['second_lang'] == "")) {
			$conditions[] = "second_lang = ?";
			$params[] = $_POST['second_lang'];
		}
		
		$cond_str = implode(" AND ", $conditions);
		
		$sql = sprintf("SELECT fname, lname, year_born, gender, start_date, end_date, min_rate, first_lang, second_lang, id FROM cs340final_project.tutor WHERE %s ORDER BY lname, fname", $cond_str);
		
		if (!($stmt = $mysqli->prepare($sql))) {
			echo "Prepare failed" . $stmt->errno . " " . $stmt->error;
		} else {
			$params = array_merge(array(str_repeat('s', count($params))), array_values($params));
			
			call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));

			$stmt->execute();
			$stmt->bind_result($fname, $lname, $year_born, $gender, $start_date, $end_date, $min_rate, $first_lang, $second_lang, $id);
			
			//need to fetch each row here and accumulate into an array
			$result = array();
			while ($stmt->fetch()){
				$result[] = array("fname" => $fname, "lname" => $lname, "year_born" => $year_born, "gender" => $gender, "start_date" => $start_date, "end_date" => $end_date, "min_rate" => $min_rate, "first_lang" => $first_lang, "second_lang" => $second_lang, "id" => $id);
			}
			
			$stmt->close();
		}
		
		return $result/*2D array that contains each tutor and stats*/;
		
	}

	function refValues($arr){
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
	
	
?>
	
	<div class="button"><a href="main.php">Back To Main</a></div>
	
</body>

</html>