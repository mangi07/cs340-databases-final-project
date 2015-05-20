/*
AUTHOR:	Benjamin R. Olson
DATE:	March 8, 2015
COURSE: CS 290 - Web Development, Oregon State University
*/

/*
Date api used to select dates:
  http://www.eyecon.ro/datepicker/#implement
*/

/*MAY NOT USE MOST OF THIS FILE !!*/

//VARIABLES
var map;
var markers = new Array();
var userData;
var latitude, longitude;
var html_loc;
var popup;
var allUsers;
var html_users;
var other_user_data;

//sets location variables and
	//  returns an html string of location info from userData
	function locationString (userData) {
		name = userData.entries.loc_name;
		
		start = userData.entries.timeframe.start;
		end = userData.entries.timeframe.end;
		
		latitude = parseFloat(userData.entries.coords.lat);
		longitude = parseFloat(userData.entries.coords.lng);
		
		html_string = 
			"<strong>Name: </strong>" + name + "<br>" +
			"<strong>Start Date: </strong>" + start + "<br>" +
			"<strong>End Date: </strong>" + end + "<br>" +
			"<strong>Latitude: </strong>" + latitude + "<br>" +
			"<strong>Longitude: </strong>" + longitude + "<br>"
		;
		
		return html_string;
	
	}


window.onload = function (){

	//data RETURNS JSON OBJECT STRING
	$.post( "userData.php", function( data ) {
		userData = JSON.parse(data);
		if (userData.entries.coords != undefined) {
			html_loc = locationString(userData);
			initMap(latitude, longitude);
			showLocation(html_loc);
		} else {
			initMap(5, 5);
		}
	});
	
	
	
	
	//showLocation must be called to set location prior to this:
	function initMap (lat, lng) {
		map = L.map('map', {
			center: [lat, lng],
			zoom: 2,
		});
		
		L.tileLayer('http://api.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwMTMiLCJhIjoieHFnSkh1RSJ9.pbbOa2J6sV8g_qLAr0E45Q', {
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/bysa/2.0/">CCBYSA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
			maxZoom: 18
		}).addTo(map)
		
		map.on('click', onMapClick);
	}
	
	//places a pin on the map
	function showLocation (html_string) {
		
		markers[0] = L.marker([latitude, longitude]);
		markers[0].bindPopup(html_string);
		map.addLayer(markers[0]);
	}
			
	
	//GET COORDINATES FOR A NEW LOCATION TO BE ADDED TO USER'S MAP
	popup = L.popup();
	function onMapClick(e) {
		popup
		.setLatLng(e.latlng)
		.setContent("Location of " + e.latlng.toString() + " to be saved.")
		.openOn(map);
		$('#lat').html(e.latlng.lat);
		$('#lng').html(e.latlng.lng);
	}
	

	var dateDivs = ['#startDate', '#endDate'];
	for (var x = 0; x < 2; x++){
		$(dateDivs[x]).DatePicker({
			flat: true,
			format:'m/d/Y',
			date: '03-04-2015',
			current: '03-04-2015',
			starts: 0,
			onBeforeShow: function(){
				$(dateDivs[x]).DatePickerSetDate($(dateDivs[x]).val(), true);
			}
		});
	}
	
	
	
	
	//FUNCTION TO ADD A JSON LOCATON OBJECT TO THE LOCATION ARRAY
	//  AND THEN SAVE THE JSON LOCATION OBJECT FOR THE CURRENT USER
	$('button.ajax').on('click', function() {
		
		//SET TO TRUE IF THERE ARE ANY USER INPUT ERRORS
		var errors = false;
		var errors_string = "";
		
		//CLEAR ANY POSSIBLE ERRORS FROM PREVIOUS CLICKS
		$('#newEntryErrors').html("");
		
		//GET AND FILTER DATE RANGE INPUT
		var startDate = $('#startDate').DatePickerGetDate(true);
		var endDate = $('#endDate').DatePickerGetDate(true);
		//adapted from: http://stackoverflow.com/questions/5619202/converting-string-to-date-in-js
		var parts = startDate.split('/');
		var start = new Date(parts[2],parts[0]-1,parts[1]);
		parts = endDate.split('/');
		var end = new Date(parts[2],parts[0]-1,parts[1]);
		
		//check input before sending
		if (start > end){
			errors_string += "Error: Start date is later than end date!<br><br>";
			errors = true;
		}
		
		//GET AND FILTER LOCATION NAME INPUT
		latitude = $('#lat').html();
		longitude = $('#lng').html();
		if (latitude == 'Latitude: not yet selected.' && 
			longitude == 'Longitude: not yet selected.') {
			errors_string += "Error: No latitude or longitude specified.<br>Click on the map to get coordinates.<br><br>";
			errors = true;
		}

		//GET AND FILTER LOCATION INPUT
		loc_name = $('#loc_name').val();
		loc_name.trim();
		if (loc_name == ''){
			errors_string += "Error: No location name provided.<br><br>";
			errors = true;
		}
		
		//STOP HERE IF THERE ARE ANY USER INPUT ERRORS,
		//  AND DISPLAY THESE ERRORS TO THE USER
		if (errors) {
			$('#newEntryErrors').html(errors_string);
			return;
		} else {
		//CREATE THE JSON OBJECT STRING FROM THE PRECEDING VARIABLES
			loc = { "entries":
					{ "coords": {"lat":latitude, "lng":longitude},
					"loc_name": loc_name,
					"timeframe": {"start":startDate, "end":endDate} 
					}
				};
			JSON.stringify(loc);
		}
		
		
		//POST THE JSON TO THE USER'S ACCOUNT ON THE SERVER
		//get response from this post as json to load locations on map (refresh map)
		$.post( "update.php", {loc:loc} )
			.done(function( data ) {
				//REMOVE THE OLD MARKER FROM THE MAP
				if (markers[0] != null) {
					map.removeLayer(markers[0]);
				}
				
				//REMOVE THE POPUP FROM THE MAP
				if (popup != null) {
					popup._close();
				}
				
				//ADD THE NEW LOCATION TO THE MAP
				userData = loc;
				var html_loc = locationString(userData);
				showLocation(html_loc);
			})
			.fail(function( data ) {//HAVE DATA RETURN ERROR MESSAGES
				$('#newEntryErrors').html(data);
			});
		
	
	});
	
	
	//FUNCTION TO GET ALL USER DATA INTO A JSON OBJECT
	//  TO PREPARE FOR FILTERING
	$('button.allUsers').on('click', function() {
		
		//GET ALL USER DATA AND CREATE SELECTIONS
		$.post( "allUsers.php" )
			.done(function( data ) {
				allUsers = JSON.parse(data);
				//CREATE CHECKBOXES TO FILTER DATA
				html_users = "";
				var name;
				var none_visible = true;
				for (user in allUsers) {
					//also filter to get only users who chose to be visible
					if (allUsers[user][2]) {
						name = allUsers[user][0];
						html_users += "<p><input type='checkbox' name='users' value='" + name + "'>" + name + "</p>";
						none_visible = false;
					}
					
				}
				
				//DISPLAY RESULTS OF SEARCH
				if (none_visible){
					html_users = "<p>None visible.</p>"
				} else {
					html_users += "<button class='button' onclick='showUsers();'>Show Users on Map</button>";
				}
				$('#user_checkboxes').html(html_users);
				
			})
			.fail(function( data ) {//HAVE DATA RETURN ERROR MESSAGES
				$('#allUsersErrors').html(data);
			});
			
	});
	
	//FUNCTION TO TOGGLE THE VISIBILITY OF THE CURRENT USER
	$('#visibility').on('click', function() {
		if ($('#visibility').prop('value') == "visible") {
			//alert($('#visibility').prop('value'));
			$.post("changeVisibility.php", {visible:"0"})
				.done( function() {
					$('#visibility').prop('value', 'hidden');
					$('#privacy_notice').html('Your location is hidden.');
					$('#visibility').html('Share My Location');
				})
				.fail( function( data ) {
					$('#vis_errors').html(data);
				});
		} else if ($('#visibility').prop('value') == "hidden") {
			//alert($('#visibility').prop('value'));
			$.post("changeVisibility.php", {visible:"1"})
				.done( function() {
					$('#visibility').prop('value', 'visible');
					$('#privacy_notice').html('Your location is visible to other users.');
					$('#visibility').html('Hide My Location');
				})
				.fail( function( data ) {
					$('#vis_errors').html(data);
				});
		}
	});
	
	
	//LOGOUT
	$('#logout').on('click', function() {
		$.post( "logout.php" );
	});
	
	
	
	
	
}

function showUsers() {
		var name_selected;
		//modified from http://stackoverflow.com/questions/590018/getting-all-selected-checkboxes-in-an-array
		$("input:checkbox[name='users']:checked").each(function() {
			
			//Clear markers array of all other users, if needed.
			//  (The first marker in markers array is the current user.)
			while (markers.length > 1) {
				map.removeLayer(markers.pop());
			}
			
			var name_selected = ($(this).val());
			for (user in allUsers) {
				if (name_selected == allUsers[user][0]){
					other_user_data = allUsers[user][1];
					var html_string = "User:" + name_selected + "<br>";
					html_string += locationString(other_user_data);
					
					//add this user's location to the map
					var lat = other_user_data.entries.coords.lat;
					var lng = other_user_data.entries.coords.lng;
					var len = markers.push(L.marker([lat, lng]));
					markers[len-1].bindPopup(html_string);
					map.addLayer(markers[len-1]);
				}
			}
		});
	}


	