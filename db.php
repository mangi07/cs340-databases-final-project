<?php

include 'storedInfo.php';

$mysqli = new mysqli("localhost", "root", $myPassword, "cs340final_project");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

//close this connection with $mysqli->close(); in files in which it is used.

?>


