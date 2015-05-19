/*
AUTHOR:	Benjamin R. Olson
DATE:	March 8, 2015
COURSE: CS 290 - Web Development, Oregon State University
*/


window.onload = function (){

	$('#student_login').on('click', function(){login(true, "student");});
	$('#create_student').on('click', function(){login(false, "student");});
	
	$('#tutor_login').on('click', function(){login(true, "tutor");});
	$('#create_tutor').on('click', function(){login(false, "tutor");});
			
}

function login(login_attempt, user_type){
	
	if (user_type == "student"){ 
		username = $("#student_userfield").val();
		password = $("#student_passfield").val();
	} else if (user_type == "tutor") {
		username = $("#tutor_userfield").val();
		password = $("#tutor_passfield").val();
	}
	
	$.post( "accounts.php", { login_attempt:login_attempt, username:username, password:password, user_type:user_type })
		.done(function( data ) {
			if ( data.trim() == "success" ){
				//attempt to direct to main.php
				window.location.replace("main.php");
			} else if (user_type == "student") {
				$('#student_errors').html(data);
				//clear login fields
				$("#student_userfield").val("");
				$("#student_passfield").val("");
			} else if (user_type == "tutor") {
				$('#tutor_errors').html(data);
				//clear login fields
				$("#tutor_userfield").val("");
				$("#tutor_passfield").val("");
			}
		})
		.fail(function() {
			$('#tutor_errors').html("Failed to communicate with the server.");
		});
			
}


