<?php
if($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . ($_SERVER['SERVER_PORT']!=80?":{$_SERVER['SERVER_PORT']}":'').$_SERVER["REQUEST_URI"]);
    exit();
}
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
<SCRIPT src="./js/jquery-3.6.0.min.js"></SCRIPT>
<SCRIPT src="./js/4xgacct.js"></SCRIPT>
<SCRIPT>
function onClickMenuItem(view) {
	var ajax = $.post("changeindexview.php",{indexview:view, jquery:"jquery"});
	ajax.done(function(data) {
		$("#pagecontent").empty().append(data);
	});
}
</SCRIPT>
<DIV id="pagecontent">
Loading....
</DIV>
<DIV class="copyright">
All game code and base content is owned by Michael J. Sabal, copyright 2014-2022.  Contributed content is owned by the contributor and used with permission.
</DIV>
</BODY>
</HTML>
