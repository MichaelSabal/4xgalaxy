<?php
session_start();

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
<LI>Offer new human player form</LI>
<LI>Create human player screens</LI>
<LI>Create CRON.php
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
function showNewUserRegistration() {
	$rtn =
'<DIV class="maincontent" id="registrationForm">
All fields are required.  The email address must not already be registered, but the screen name does not need to be unique.<BR />
<LABEL for="firstname">First Name</LABEL><INPUT id="firstname" type="text" name="firstname" /><BR />
<LABEL for="lastname">Last Name</LABEL><INPUT id="lastname" type="text" name="lastname" /><BR />
<LABEL for="email">E-mail Address</LABEL><INPUT id="email" type="text" name="email" /><BR />
<LABEL for="alias">Screen Name</LABEL><INPUT id="alias" type="text" name="alias" /><BR />
<LABEL for="month">Birthdate (MM/DD/YYYY)</LABEL>';
	$rtn .= '<SELECT id="month">';
	for ($i=1;$i<=12;$i++) $rtn .= '<OPTION value="'.$i.'">'.$i.'</OPTION>';
	$rtn .= '</SELECT> / ';
	$rtn .= '<SELECT id="dayofmonth">';
	for ($i=1;$i<=31;$i++) $rtn .= '<OPTION value="'.$i.'">'.$i.'</OPTION>';
	$rtn .= '</SELECT> / ';
	$rtn .= '<SELECT id="century">';
	for ($i=19;$i<=20;$i++) $rtn .= '<OPTION value="'.$i.'">'.$i.'</OPTION>';
	$rtn .= '</SELECT>';
	$rtn .= '<SELECT id="year">';
	for ($i=0;$i<=99;$i++) $rtn .= '<OPTION value="'.sprintf('%02d',$i).'">'.sprintf('%02d',$i).'</OPTION>';
	$rtn .= '</SELECT>';
	$rtn .=
'<BR /><LABEL for="paw1">Password</LABEL><INPUT id="paw1" type="password" name="paw1" /><BR />
<LABEL for="paw2">Repeat password</LABEL><INPUT id="paw2" type="password" name="paw2" /><BR />
<BR /><BUTTON id="register" class="menuitem" onClick="onSubmitRegister();">REGISTER</BUTTON>
<BUTTON id="login" class="menuitem" name="login" onClick="onClickLoginButton();">LOGIN</BUTTON>
<BUTTON id="cancel" class="menuitem" name="cancel" onClick="onClickMenuItem(1);">CANCEL</BUTTON><BR />
</DIV>';
	echo $rtn;
}
function showUserLogin() {
	$rtn = 
'<DIV class="maincontent" id="loginForm">
<LABEL>Username or e-mail address</LABEL><BR />
<INPUT id="username" type="text" name="username" /><BR />
<LABEL>Password</LABEL><BR />
<INPUT id="paw" type="password" name="paw" /><BR />
<BR /><BUTTON id="register" class="menuitem" onClick="onClickRegisterButton();">NEW USER?</BUTTON>
<BUTTON id="login" class="menuitem" name="login" onClick="onSubmitLogin();">LOGIN</BUTTON><BR />
</DIV>';
	echo $rtn;
}
function registerUser() {
	
}
function userDashboard() {
	
}
function getSalt1() {
	
}
function doesLoginExist() {
	
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
			case 2: showNewUserRegistration(); break;
			case 3: showUserLogin(); break;
			case 4: registerUser(); break;
			case 5: userDashboard(); break;
		}
	}
	$dbconn->close();
}
//var_dump($_POST);
if (isset($_POST['jquery'])) jquery();
?>