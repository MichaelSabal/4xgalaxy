<?php
session_start();
$view=1;
if (isset($_SESSION['gmview'])) $view=$_SESSION['gmview'];
?>
<!DOCTYPE HTML>
<HTML>
<HEAD>
<TITLE>4x Galaxy / Game Master</TITLE>
<META http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<LINK rel="stylesheet" type="text/css" href="gm.css" />
</HEAD>
<BODY onLoad="onClickMenuItem(1);">
<SCRIPT src="../js/jquery-1.11.2.min.js"></SCRIPT>
<SCRIPT>
function onClickMenuItem(view) {
	var ajax = $.post("changeview.php",{gmview:view, jquery:"jquery"});
	ajax.done(function(data) {
		$("#managementPane").empty().append(data);
	});
	$(".menuitem").removeClass("selected");
	$("#gmview"+view).addClass("selected");
}
function onClickGrowGalaxyButton() {
		var ajax = $.post("galaxymanager.php",{jquery:"jquery", op:"growgalaxy"});
		ajax.done(function(data) {
			onClickMenuItem(<?php echo $view; ?>);
		});
}
function onClickAddStarButton() {
		var ajax = $.post("galaxymanager.php",{jquery:"jquery", op:"addstar"});
		ajax.done(function(data) {
			onClickMenuItem(<?php echo $view; ?>);
		});	
}
function onClickAddComputerPlayerButton() {
		var ajax = $.post("galaxymanager.php",{jquery:"jquery", op:"addcomputerplayer"});
		ajax.done(function(data) {
			onClickMenuItem(<?php echo $view; ?>);
		});
}
function onClickStarID(starID) {
	var ajax = $.post("changeview.php",{gmview:7, jquery:"jquery", starID: starID});
	ajax.done(function(data) {
		$("#managementPane").empty().append(data);
	});
}
function onClickPlayerID(playerID) {
	var ajax = $.post("changeview.php",{gmview:8, jquery:"jquery", playerID: playerID});
	ajax.done(function(data) {
		$("#managementPane").empty().append(data);
	});
}
function onClickHOID(starID, HOID) {
	var ajax = $.post("changeview.php",{gmview:9, jquery:"jquery", starID: starID, hoID:HOID});
	ajax.done(function(data) {
		$("#managementPane").empty().append(data);
	});
}
</SCRIPT>
<DIV id="menubar">
<A class="menuitem <?php if ($view==1) echo 'selected'; ?>" id="gmview1" onClick="onClickMenuItem(1);">DASHBOARD</A>
<A class="menuitem <?php if ($view==2) echo 'selected'; ?>" id="gmview2" onClick="onClickMenuItem(2);">STAR LIST</A>
<A class="menuitem <?php if ($view==3) echo 'selected'; ?>" id="gmview3" onClick="onClickMenuItem(3);">PLAYER LIST</A>
<A class="menuitem <?php if ($view==4) echo 'selected'; ?>" id="gmview4" onClick="onClickMenuItem(4);">MAP</A>
<A class="menuitem <?php if ($view==5) echo 'selected'; ?>" id="gmview5" onClick="onClickMenuItem(5);">EVENT LOG</A>
<A class="menuitem <?php if ($view==6) echo 'selected'; ?>" id="gmview6" onClick="onClickMenuItem(6);">ECONOMY</A>
</DIV>
<BR />
<DIV id="managementPane">
Loading....
</DIV>
</BODY>
</HTML>