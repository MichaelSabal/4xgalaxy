<?php
session_start();
$view=1;
if (isset($_SESSION['indexview'])) $view=$_SESSION['indexview'];
?>
<!DOCTYPE HTML>
<HTML>
<HEAD>
<TITLE>4x Galaxy</TITLE>
<META http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<LINK rel="stylesheet" type="text/css" href="css/main.css" />
</HEAD>
<BODY onLoad="onClickMenuItem(<?php echo $view; ?>);">
<SCRIPT src="./js/jquery-1.11.2.min.js"></SCRIPT>
<SCRIPT>
function onClickMenuItem(view) {
	var ajax = $.post("changeindexview.php",{indexview:view, jquery:"jquery"});
	ajax.done(function(data) {
		$("#pagecontent").empty().append(data);
	});
}
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
	// Validate birthdate is a real date at least five years in the past
	$thisyear = new Date().getFullYear();
	$minyear = $thisyear - 120;
	$maxyear = $thisyear - 5;
	$selectedyear = $("#century").val() + $("#year").val();
	if ($selectedyear < $minyear || $selectedyear > $maxyear) {
		//alert("Sorry, there's nobody alive born before "+$minyear+", and we'd prefer our players be at least 5 years old.  Thanks.");
		alert("Thisyear: "+$thisyear+", minyear: "+$minyear+", maxyear: "+$maxyear+", selectedyear: "+$selectedyear);
		return;
	} 
	$selectedmonth = $("#month").value();
	$selectedday = $("#dayofmonth").value();
	try {
		$.datepicker.parseDate('mm/dd/yy', $selectedmonth+"/"+$selectedday+"/"+$selectedyear);
	} catch(e) {
		alert($selectedmonth+"/"+$selectedday+"/"+$selectedyear+" is not a valid date.");
		return;
	}
	// Validate email does not exist
	// All validations passed Javascript testing.
	// Get Salt 1
	// Hash salted password.
	// Submit registration.
}
function onSubmitLogin() {
	
}
</SCRIPT>
<DIV id="pagecontent">
Loading....
</DIV>
<DIV class="copyright">
All game code and base content is owned by Michael J. Sabal, copyright 2014-2016.  Contributed content is owned by the contributor and used with permission.
</DIV>
</BODY>
</HTML>