/*
AUTHOR:	Benjamin R. Olson
DATE:	May 31, 2015
COURSE: CS 340 - Web Development, Oregon State University
*/


/*jquery must be included before this works!*/

window.onload = function (){

	$('.student_login').on('click', function(){login("student");});
	$('.tutor_login').on('click', function(){login("tutor");});
	
	$('#create_user').on('click', function(){create_user();});

}

function create_user(){

	//code modified from http://api.jquery.com/jquery.post/
	$( "#new_user_form" ).submit(function( event ) {
		// Stop form from submitting normally
		event.preventDefault();
	});
	
	$.post( "accounts.php", $( "#new_user_form" ).serialize() )
		.done(function( data ) {
			if ( data.trim() == "success" ){
				//attempt to direct to main.php
				window.location.replace("main.php");
			} else {
				$('#errors').html(data);
			}
		})
		.fail(function() {
			$('#errors').html("Failed to communicate with the server.");
		});
}

function login(user_type){
	
	if (user_type == "student"){ 
		username = $("#student_userfield").val();
		password = $("#student_passfield").val();
	} else if (user_type == "tutor") {
		username = $("#tutor_userfield").val();
		password = $("#tutor_passfield").val();
	}
	
	$.post( "accounts.php", { login_attempt:"true", username:username, password:password, user_type:user_type })
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


