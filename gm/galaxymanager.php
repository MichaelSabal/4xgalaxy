<?php
session_start();

function growTheGalaxy($dbconn) {
	$g = new Galaxy($dbconn);
	$g->growGalaxy(5);
	$g->save();
}
function addAStar($dbconn) {
	$sm = new StarManager($dbconn);
	$sm->addStars(1);
}
function addAComputerPlayer($dbconn) {
	$pm = new PlayerManager($dbconn);
	$pm->addComputerPlayer();
}
function jquery() {
	include_once('../../4xgalaxy.php');
	spl_autoload_register(function ($class) {
		include '../classes/' . $class . '.php';
	});
	if (!isset($dbname)) {
		echo 'No database has been set up.  I\'m afraid we can\'t play the game.';
	} else {
		$dbconn = new mysqli();
		$dbconn->connect($dbhost,$dbuser,$dbauth,$dbname);
		if ($dbconn===false) {
			echo '<div class="problem">The database is offline!</div>';
			return;
		} 
		if (!isset($_POST['op'])) return;
		$op = $_POST['op'];
		if ($op=='growgalaxy') growTheGalaxy($dbconn);
		if ($op=='addstar') addAStar($dbconn);
		if ($op=='addcomputerplayer') addAComputerPlayer($dbconn);
	}
	$dbconn->close();
}
if (isset($_POST['jquery'])) jquery();
?>