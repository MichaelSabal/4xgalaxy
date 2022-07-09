<?php
session_start();
function isValidEmail(string $email): bool {
	// Pattern from http://regexlib.com/REDetails.aspx?regexp_id=26
	$pattern = '/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
	return preg_match($pattern,$email);
}
function showLandingPage() {
	if (isset($_SESSION['authuser'])) return userDashboard();
	$rtn =
'<DIV class="maincontent" id="frontpage">
<DIV id="menubar">
<BUTTON class="menuitem" id="register" onClick="onClickRegisterButton();">NEW PLAYER?</BUTTON>
<BUTTON class="menuitem" id="login" onClick="onClickLoginButton();">LOG IN TO PLAY</BUTTON>
</DIV><BR />
<DIV id="aboutgame">
<B>What is 4xGalaxy?</B><BR />
As the name suggests, this is a classic 4x game: explore, expand, exploit, exterminate.  What makes this game somewhat different,
is that this game is much more about working together with other players than trying to wipe them off the map.  For every human
player, there is also a computer player.  The game master also has the option to add additional computer players if the human
players are becoming too strong.  As more players are added to the game, and as existing players continue to expand their territories,
the map itself expands, introducing new stars to explore and exploit. <BR /><BR />
Many of the technologies and resources in the game are created by the players themselves.  These can then be exchanged for other
resources that may be of value.  While the game is rather slow to start, focusing on research and economics, the time will come
when you must defend yourself against the attacks of other players, both human and computer.  The ultimate goal of the game
is to keep the computer players from having the upper hand.  <BR /><BR />
If you want to host your own server, the game is open source, easy to set up, and easy to modify.  This makes it ideal for
classrooms and dorms, supporting anywhere from a handful to hundreds of players on one server.  This is a game of strategy,
of using your resources and your words well.  There are no hi-def 3D animations here.  The battles are not fast paced.
This is not a game for those with short attention spans; but for those determined to go the distance.  Are you that person?
Start playing today.
</DIV>
</DIV>
<DIV class="maincontent" id="todolist">
There is certainly much to be done to make this game playable.  Here is a working list.
<OL>
<LI style="color: #007F00;">Create Nation class.</LI>
<LI style="color: #007F00;">Create Resource class.</LI>
<LI style="color: #007F00;">Create Technology class.</LI>
<LI style="color: #007F00;">Create PlanetManager class.</LI>
<LI>Create ResourceManager class.</LI>
<LI>Create TechnologyManager class.</LI>
<LI style="color: #007F00;">On player creation, create a nation on the planet.</LI>
<LI style="color: #007F00;">Assign a population to the nation, sum all nations and assign to the planet.</LI>
<LI style="color: #007F00;">Make a planet view for the gm dashboard</LI>
<LI>Give essential resources and technologies to the new nation.</LI>
<LI style="color: #007F00;">Offer new human player form</LI>
<LI>Create human player screens</LI>
<LI style="color: #007F00;">Create CRON.php
<OL>
<LI>Adjust HO thetas</LI>
<LI>Adjust NationResources</LI>
<LI>Adjust Population</LI>
<LI>Adjust Technology progress</LI>
</OL></LI>
<LI>Create Map page</LI>
<LI>Add moons, asteroids, rings, and comets to the HO table.</LI>
<LI>Create administrator, gm, and player manuals</LI>
</OL>
</DIV>
<DIV class="maincontent" id="ideas">
Some ideas for later versions:
<OL>
<LI>Add random events like war, discovery of new resources, disease</LI>
<LI>Create lessons plans for sale based on the game.</LI>
<LI>Create a priest system</LI>
<LI>Include a working, programmable in-game computer</LI>
<LI>Appify the game</LI>
</OL>
</DIV>';
	echo $rtn;
}
function showNewUserRegistration(): string {
	$rtn =
'<DIV class="maincontent" id="registrationForm">
All fields are required.  The email address must not already be registered, but the screen name does not need to be unique.<BR />
<LABEL for="firstname">First Name</LABEL><INPUT id="firstname" type="text" name="firstname" /><BR />
<LABEL for="lastname">Last Name</LABEL><INPUT id="lastname" type="text" name="lastname" /><BR />
<LABEL for="email">E-mail Address</LABEL><INPUT id="email" type="email" name="email" /><BR />
<LABEL for="alias">Screen Name</LABEL><INPUT id="alias" type="text" name="alias" /><BR />
<LABEL for="month">Birthdate</LABEL><INPUT id="birthdate" type="date" name="birthdate"  /><BR />
<LABEL for="paw1">Password</LABEL><INPUT id="paw1" type="password" name="paw1" /><BR />
<LABEL for="paw2">Repeat password</LABEL><INPUT id="paw2" type="password" name="paw2" /><BR />
<BR /><BUTTON id="register" class="menuitem" onClick="onSubmitRegister();">REGISTER</BUTTON>
<BUTTON id="login" class="menuitem" name="login" onClick="onClickLoginButton();">LOGIN</BUTTON>
<BUTTON id="cancel" class="menuitem" name="cancel" onClick="onClickMenuItem(1);">CANCEL</BUTTON><BR />
</DIV>';
	return $rtn;
}
function showUserLogin() {
	$rtn =
'<DIV class="maincontent" id="loginForm">
<LABEL>E-mail address</LABEL><BR />
<INPUT id="username" type="email" name="username" /><BR />
<LABEL>Password</LABEL><BR />
<INPUT id="paw" type="password" name="paw" /><BR />
<BR /><BUTTON id="register" class="menuitem" onClick="onClickRegisterButton();">NEW USER?</BUTTON>
<BUTTON id="login" class="menuitem" name="login" onClick="onSubmitLogin();">LOGIN</BUTTON><BR />
</DIV>';
	echo $rtn;
}
function registerUser(MySQLi $dbconn) {
	$firstname = $_POST['firstname'] ?? null;
	$lastname = $_POST['lastname'] ?? null;
	$alias = $_POST['alias'] ?? null;
	$email = $_POST['email'] ?? null;
	$birthdate = $_POST['birthdate'] ?? null;
	$paw1 = $_POST['paw1'] ?? null;
	$paw2 = $_POST['paw2'] ?? null;
	$error = "error";
	// Revalidate all fields
	if (!isset($firstname,$lastname,$alias,$email,$birthdate,$paw1,$paw2)) {
		echo "<DIV class='$error'>All fields must be filled in.</DIV>".showNewUserRegistration();
		return;
	}
	if ($paw1 !== $paw2) {
		echo "<DIV class='$error'>Passwords must match.</DIV>".showNewUserRegistration();
		return;
	}
	if (strlen($paw1)<10) {
		echo "<DIV class='$error'>Passwords must be at least 10 characters long.</DIV>".showNewUserRegistration();
		return;
	}
	// TODO: Check password against top 10K list
	try {
		$bd = new DateTime($birthdate);
	} catch (Exception $e) {
		echo "<DIV class='$error'>Birthdate is not a valid date.</DIV>".showNewUserRegistration();
		return;
	}
	if (!isValidEmail($email)) {
		echo "<DIV class='$error'>Please provide a valid email address.</DIV>".showNewUserRegistration();
		return;
	}
	$player = new Player($dbconn);
	$id = $player->lookupByEmail($email);
	if ($id>-1) {
		echo "<DIV class='$error'>This email address is already in use.</DIV>".showNewUserRegistration();
		return;
	}
	$player->setType('H');
	$player->setEmail($email);
	$player->setAlias($alias);
	$player->setBirthday($bd);
	$player->setFirstName($firstname);
	$player->setLastName($lastname);
	$player->setSecret($paw1);
	$player->setDateJoined();
	$player->setLastLogin();
	if ($player->save()) {
		$starmgr = new StarManager($dbconn);
		$id = $player->getID();
		$starid = $starmgr->assignFirstStar($id,'H');
		if ($starid>0) {
			$player->setFirstStar($starid);
			$player->save();
		}
		userDashboard($dbconn);
	} else {
		echo "<DIV class='$error'>The user could not be created.</DIV>".showNewUserRegistration();
	}
}
function userDashboard(MySQLi $dbconn) {
	$_SESSION['indexview'] = 5;
	showLandingPage();
}
function doesLoginExist(MySQLi $dbconn) {
	$_SESSION['indexview'] = 2;
	$player = new Player($dbconn);
	if (!isset($_POST['email'])) {
		echo 'No email address was provided.';
		return;
	}
	$email = $_POST['email'];
	if (!isValidEmail($email)) {
		echo 'Please provide a valid email address';
		return;
	}
	$id = $player->lookupByEmail($email);
	if ($id==-1) {
		echo 'success';
		return;
	}
	echo 'fail';
}
function authenticateUser(MySQLi $dbconn) {

}
function jquery() {
	include_once('../4xgalaxy.php');	// Database config file
	spl_autoload_register(function ($class) {
		include 'classes/' . $class . '.php';
	});
	if (!isset($dbname)) {
		echo 'No database has been set up.  I\'m afraid we can\'t play the game.';
	} else {
		$dbconn = new mysqli();
		$dbconn->connect($dbhost,$dbuser,$dbauth,$dbname);
		if (isset($_POST['indexview'])) {
			$view = $_POST['indexview'];
			if (!is_numeric($view)) return;
			$_SESSION['indexview'] = $view;
		} elseif (isset($_SESSION['indexview'])) $view = $_SESSION['indexview'];
		else return;
		if ($dbconn===false) {
			echo '<div class="problem">The database is offline!</div>';
			return;
		}

		switch ($view) {
			case 1: showLandingPage(); break;
			case 2: echo showNewUserRegistration(); break;
			case 3: showUserLogin(); break;
			case 4: registerUser($dbconn); break;
			case 5: userDashboard($dbconn); break;
			case 6: doesLoginExist($dbconn); break;
			case 7: authenticateUser($dbconn); break;
		}
	}
	$dbconn->close();
}
//var_dump($_POST);
if (isset($_POST['jquery'])) jquery();
?>
