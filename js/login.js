var id_token = -1;
$(document).ready(function() {

	sendPost("./php/login.php", "checkSession", "", function(xhr) {
		//console.log(xhr.responseText);
		var response = JSON.parse(xhr.responseText);
		console.log(response);
		if(response.sessionStarted) {
			logedIn = true;
			$("#signIn-btn").hide();
			$("#signOut-btn").show();
			id_token = response.id_token;
		}
	});

	
});

var logedIn = false;

function onSignIn(googleUser) {
	id_token = googleUser.getAuthResponse().id_token;

	sendPost("./php/login.php", "signIn", "id_token=" + id_token, function(xhr) {
		console.log("Signed in as: " + xhr.responseText);
		var returnObj = JSON.parse(xhr.responseText);
		alert(returnObj.msg);

		if(returnObj.success) {
			logedIn = true;
			$("#signIn-btn").hide();
			$("#signOut-btn").show();
		}
	}, async = true);
}

function signOut() {
	var auth2 = gapi.auth2.getAuthInstance();
	auth2.signOut().then(function() {
		console.log("User signed out");
		logedIn = false;
		$("#signIn-btn").show();
	  	$("#signOut-btn").hide();
	  	sendPost("./php/login.php", "signOut", "", function(xhr) {
	  	}, async = true);
	})
	 
}

function isUserLogedIn() {
	return logedIn;
}


function sendPost(url, fun, vars, callback, async = true) {
	var xhr = new XMLHttpRequest();
	xhr.onload = function() {
		console.log(xhr.responseText);
		callback(xhr);
	};

	xhr.open('POST', url, async);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send("fun=" + fun + (vars != ""?"&" + vars: ""));
	//console.log("fun=" + fun + (vars != ""?"&" + vars: ""));
}

function sendGet(url, fun, vars, callback, async = true) {
	var xhr = new XMLHttpRequest();
	xhr.onload = function() {
		console.log(xhr.responseText);
		callback(xhr);
	};

	xhr.open('GET', url + "?fun=" + fun + (vars != ""?"&" + vars: ""), async);
	xhr.send();
	//console.log("fun=" + fun + (vars != ""?"&" + vars: ""));
}