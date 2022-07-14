function onClickRegisterButton() {
	var ajax = $.post("changeindexview.php",{indexview:2, jquery:"jquery"});
	ajax.done(function(data) {
		$("#pagecontent").empty().append(data);
	});
}
function onClickLoginButton() {
	var ajax = $.post("changeindexview.php",{indexview:3, jquery:"jquery"});
	ajax.done(function(data) {
		$("#pagecontent").empty().append(data);
	});
}
function onSubmitRegister() {
	// Validate missing fields
	if (($("#firstname").val()=="") ||
		($("#lastname").val()=="") ||
		($("#email").val()=="") ||
		($("#alias").val()=="") ||
		($("#birthdate").val()=="") ||
		($("#paw1").val()=="") ||
		($("#paw2").val()=="")) {
			alert("All fields are required.");
			return;
	}
	// Validate passwords match
	if ($("#paw1").val()!=$("#paw2").val()) {
		alert("Passwords must match.");
		return;
	}
	if ($("#paw1").val().length < 10) {
		alert("Passwords must be at least 10 characters long");
		return;
	}
	// Validate birthdate is a real date at least five years in the past
	$thisyear = new Date().getFullYear();
	$minyear = $thisyear - 120;
	$maxyear = $thisyear - 5;
	$birthdate = new Date($("#birthdate").val());
	if ($birthdate == "Invalid Date") {
		alert("Please enter a valid date");
		return;
	}
	if ($birthdate.getFullYear() < $minyear || $birthdate.getFullYear() > $maxyear) {
		alert("Your birthdate must be between "+$minyear+" and "+$maxyear);
		return;
	}
	// Validate email does not exist
	$.post("changeindexview.php",{indexview:6, jquery:"jquery", email:$("#email").val()},function(data) {
		if (data=="success") {
			// All validations passed Javascript testing.
			// Submit registration.
			var data = {
				indexview: 4
				,jquery:"jquery"
				,firstname:$("#firstname").val()
				,lastname:$("#lastname").val()
				,email:$("#email").val()
				,alias:$("#alias").val()
				,birthdate:$("#birthdate").val()
				,paw1:$("#paw1").val()
				,paw2:$("#paw2").val()
			};
			var ajax = $.post("changeindexview.php",data);
			ajax.done(function(data) {
				$("#pagecontent").empty().append(data);
			});
		} else if (data=="fail"){
			alert("Your email address is already registered.  Have you forgotten your password?");
			return;
		} else {
			alert(data);
			return;
		}
	});
}
function onSubmitLogin() {
	var data = {
		indexview: 7
		,jquery:"jquery"
		,username:$("#username").val()
		,paw:$("#paw").val()
	};
	$.post("changeindexview.php",data,function(response) {
		if (response=="success") $.post("changeindexview.php",{indexview:5,jquery:"jquery"},function(data) {
			$("#pagecontent").empty().append(data);
		});
		else alert("Authentication failed.");
	});
}
