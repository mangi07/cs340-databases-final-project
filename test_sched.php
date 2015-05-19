<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>CS 340 Sched Test - Ben R. Olson</title>
</head>

<body>
  <?php
    ini_set('display_errors', 'On');

    include ("db.php");
	
	$sched = array();
	$sched[0] = '000001111100000111110000011111000001111100000000';
	$sched[1] = '111110000011111000001111100000111110000011111111';
	$sched[2] = '000001111100000111110000011111000001111100000000';
	$sched[3] = '111110000011111000001111100000111110000011111111';
	$sched[4] = '000001111100000111110000011111000001111100000000';
	$sched[5] = '111110000011111000001111100000111110000011111111';
	$sched[6] = '000001111100000111110000011111000001111111111111';
	
	
	$user_name = 'frankie';
	$user2 = 'bobby';
	/*
	insert_weekly_sched($sched, $user2);
	*/
	
	$sched = find_sched_intersect($user_name, $user2); //edit this function
	echo "INTERSECTION:\n";
	var_dump($sched);
	
	//try displaying schedule (receive as int, but then convert to binary string
    echo "USER SCHEDULE: \n";
	var_dump(get_schedule($user_name));
	
  ?>

  
  
<?php

  /*
    Pre-conditions: $stmt must exist as object connecting to database
	Returns: 
  */
  function get_schedule($user_name){
    
	global $mysqli;
	
	$index = 0;
	$val = NULL;
	$sched = array();
	
	//eventually, add where __ = ? to prepared statement
	//  to indicate which student
    if(!($stmt = $mysqli->prepare("select sun, mon, tues, wed, thurs, fri, sat from test_sched.sched where user_name = ?;") )){
	  echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("s",$user_name))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	if(!$stmt->bind_result($sched[0], $sched[1], $sched[2], $sched[3], $sched[4], $sched[5], $sched[6])){
		echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->fetch();
	$stmt->close();
	
	return $sched;
	
  }
  
  
  /*
    Pre-conditions: $stmt must exist as object connecting to database
	  $sched must be an array;
	Return: a string array of
	  time slots for each day, Sunday through Saturday,
	  where each day has 48 1/2-hour time slots
	  represented as a binary string (0 unavailabe, 1 available).
  */
  function find_sched_intersect($user1, $user2){
  
    global $mysqli;
	$index = 0;
	$intersect = array();
	$d = array();
	
	//join tables between two users
	if(!($stmt = $mysqli->prepare("select tb1.*, tb2.* from
		(select sun, mon, tues, wed, thurs, fri, sat from test_sched.sched where user_name = ?) as tb1
		inner join
		(select sun, mon, tues, wed, thurs, fri, sat from test_sched.sched where user_name = ?) as tb2
		on 1;") )){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("ss",$user1,$user2))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	if(!$stmt->bind_result($d[0],$d[1],$d[2],$d[3],$d[4],$d[5],$d[6],
							$d[7],$d[8],$d[9],$d[10],$d[11],$d[12],$d[13])){
		echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->fetch();
	$stmt->close();
	
	for ($y = 0; $y < count($d)/2; $y++){
		$day = "";
		for ($z = 0; $z < strlen($d[$y]); $z++){
			if ($d[$y][$z] == $d[$y+7][$z] && $d[$y][$z] == '1')
				$day .= '1';
			else
				$day .= '0';
		}
		$intersect[$y] = $day;
	}
	
	return $intersect;
	
  }
  
  /*
    Pre-conditions: $days must contain an array of seven elements,
	  where each element is a binary string representing 48 30-minute time slots
	  in a given day.  The order of days in the array must be Sunday through Saturday.
	  
	  $user_name holds the string value of the user_name for which to insert the schedule
	Post-conditions: The database contains a weekly schedule for that user.
  */
  function insert_weekly_sched(&$days, $user_name){
    global $mysqli;
	
    if(!($stmt = $mysqli->prepare("insert into test_sched.sched(
	  user_name, sun, mon, tues, wed, thurs, fri, sat) values (
	  ?, ?, ?, ?, ?, ?, ?, ?)") )){
	echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("ssssssss",$user_name,
	  $days[0],$days[1],$days[2],$days[3],$days[4],$days[5],$days[6]))){
	  echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->close();
  }
  
  /*borrowed from mcampa at gmail dot com, posted on http://php.net/manual/en/function.decbin.php*/
  function d2b($n) {
    return str_pad(decbin($n), 48, "0", STR_PAD_LEFT);
  }

?>

</body>
</html>