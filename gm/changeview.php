<?php
session_start();
function showDashboard($dbconn) {
	include './Dashboard.php';
	$dash = new Dashboard($dbconn,'gmdashblock');
	$dash->showServerBlock('gmdashserverblock');
	$dash->showGalaxyBlock('gmdashgalaxyblock');
	$dash->showStarBlock('gmdashstarblock');
	$dash->showStarColorBlock('gmdashstarcolorblock');
	$dash->showPlayerBlock('gmdashplayerblock');
}
function showStarList($dbconn) {
	$sm = new StarManager($dbconn);
	echo $sm->listStars();
}
function showPlayerList($dbconn) {
	$pm = new PlayerManager($dbconn);
	$pm->listPlayers();
}
function showMap($dbconn) {

}
function showEventLog($dbconn) {

}
function showEconomy($dbconn) {

}
function showStarSystem($dbconn,$starID) {
	if (!is_numeric($starID) || $starID < 1) return;
	$pm = new PlanetManager($dbconn);
	$sm = new StarManager($dbconn);
	echo $sm->listStars($starID);
	echo "<BR /><BR />";
	echo $pm->listObjects($starID);
}
function showNationsInSystem($dbconn,$starID,$hoID) {
	if (!is_numeric($starID) || !is_numeric($hoID)) return;
	$pm = new PlanetManager($dbconn);
	$pm->listNationsForHO($hoID);
}
function showNationsForPlayer($dbconn,$playerID) {
	if (!is_numeric($playerID) || $playerID < 1) return;
	$pm = new PlanetManager($dbconn);
	$pm->listNationsForPlayer($playerID);
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
		if (isset($_POST['gmview'])) {
			$view = $_POST['gmview'];
			if (!is_numeric($view)) return;
			$_SESSION['gmview'] = $view;
		} elseif (isset($_SESSION['gmview'])) $view = $_SESSION['gmview'];
		else return;
		if ($dbconn===false) {
			echo '<div class="problem">The database is offline!</div>';
			return;
		}
		$starID = -1;
		$playerID = -1;
		if (isset($_POST['starID'])) $starID = $_POST['starID'];
		if (isset($_POST['playerID'])) $playerID = $_POST['playerID'];
		if (isset($_POST['hoID'])) $hoID = $_POST['hoID'];
		// Form input is always a string, which won't work with is_integer, so is_numeric has to be used.
		if (!isset($starID) || !is_numeric($starID)) $starID = -1;
		if (!isset($playerID) || !is_numeric($playerID)) $playerID = -1;
		if (!isset($hoID) || !is_numeric($hoID)) $hoID = -1;
		switch ($view) {
			case 1: showDashboard($dbconn); break;
			case 2: showStarList($dbconn); break;
			case 3: showPlayerList($dbconn); break;
			case 4: showMap($dbconn); break;
			case 5: showEventLog($dbconn); break;
			case 6: showEconomy($dbconn); break;
			case 7: showStarSystem($dbconn,$starID); break;
			case 8: showNationsForPlayer($dbconn,$playerID); break;
			case 9: showNationsInSystem($dbconn,$starID,$hoID); break;
		}
	}
	$dbconn->close();
}
// var_dump($_POST);
if (isset($_POST['jquery'])) jquery();
?>
