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


if(isset($_POST["start"]) && isset($_POST["end"]) && isset($_POST["id"])){
	
	//debug
	//echo "********$_POST[start]*******$_POST[end]***********$_POST[id]********";
	
	include("db.php");
	if(!($stmt = $mysqli->prepare("
		insert into sessions(sid, tid, start_time, end_time, rate)
		values (
		  ?,
		  (select id from tutor where user_name = ?),
		  ?, ?,
		( select rate from student_tutor where (sid, tid) = (
		  ?,
		  (select id from tutor where user_name = ?)
		) )
		)
	"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("isssis",$_POST['id'],
								$_SESSION['user'],
								$_POST['start'], $_POST['end'],
								$_POST['id'],
								$_SESSION['user']
	))){
		echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
	}else{
		$rate = get_rate();
		echo "Successful entry for session:<br>
		Start Time: $_POST[start]<br>
		End Time: $_POST[end]<br>
		Rate: $rate per hour";
	}
	$stmt->close();
	
	
}

function get_rate(){
	global $mysqli;
	if(!($stmt = $mysqli->prepare("
		select rate from student as s inner join
		student_tutor as st
		on s.id = st.sid inner join
		tutor as t
		on t.id = st.tid
		where t.user_name = ?
		limit 1
	"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!($stmt->bind_param("s",$_SESSION['user']))){
		echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->bind_result($r)){
		echo "Bind paramaters failed: " . $stmt->errno . " " . $stmt->error;
	};
	$stmt->fetch();
	$stmt->close();
	return $r;
}
?>
