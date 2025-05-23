<?php
class StarManager {
	private $dbconn = null;
	private $starArray = array();
	private $starIDs = [];
	private $starCount = 0;
	public function __construct($conn) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		$this->countStars();
		if ($this->starCount==0) {
			$this->addStars(100);
		}
	}
	public function getStarIDs() {
		return $this->starIDs;
	}
	public function echoColorListAsLabel() {
		$q = "SELECT starColor,COUNT(1) as cnt FROM Stars GROUP BY starColor;";
		$result = $this->dbconn->query($q);
		$segment = '';
		if ($result!==false) {
			$star = new Star($this->dbconn);
			while ($row=$result->fetch_assoc()) {
				$segment .= '<LABEL>'.$star->getColorName($row['starColor']).': </LABEL> '.$row['cnt'].'<BR />';
			}
		}
		return $segment;
	}
	private function countStars() {
		$q = "SELECT COUNT(1) as cnt FROM Stars;";
		$result = $this->dbconn->query($q);
		if ($result===false) return -1;
		$row = $result->fetch_assoc();
		$this->starCount = $row['cnt'];
	}
	public function getCount() {
		return $this->starCount;
	}
	public function getHabitableCount() {
		$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Stars
			WHERE starColor IN ('OR','YG','RD');");
		if ($result===false) return -1;
		$row = $result->fetch_assoc();
		return $row['cnt'];
	}
	public function getHumanCount() {
		$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Stars s
			JOIN Players p ON s.playerID=p.playerID
			WHERE p.playerType IN ('A','M','H');");
		if ($result===false) return '0';
		$row = $result->fetch_assoc();
		return $row['cnt'];
	}
	public function getAICount() {
		$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Stars s
			JOIN Players p ON s.playerID=p.playerID
			WHERE p.playerType='C';");
		if ($result===false) return '0';
		$row = $result->fetch_assoc();
		return $row['cnt'];
	}
	public function addStars($howmany) {
		if (!is_numeric($howmany)) return;
		$galaxy = new Galaxy($this->dbconn);
		while ($this->starCount+$howmany > $galaxy->getMaxStars()) $galaxy->growGalaxy(50);
		$gs = $galaxy->getSize();
		$ms = $galaxy->getMinimumSeparationOfStars();
		$eventlog = new EventLog($this->dbconn);
		for ($i=1;$i<=$howmany;$i++) {
			$s = new Star($this->dbconn);
			$s->pickColor();
			$s->pickRandomName();
			$s->pickRadius();
			$locok = false;
			while (!$locok) {
				$x = rand(1000*(0+($ms/2)),($gs[0]-($ms/2))*1000)/1000;
				$y = rand(1000*(0+($ms/2)),($gs[1]-($ms/2))*1000)/1000;
				$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Stars
					WHERE sqrt(power(locationX-$x,2)+power(locationY-$y,2))<$ms;");
				if ($result!==false) {
					$row = $result->fetch_assoc();
					if ($row['cnt']==0) $locok=true;
				} else $locok = true; // This is a bad idea, but necessary to prevent an infinite loop
			}
			$s->setLocation($x,$y);
			$s->save();
			$this->starArray[] = $s;
			$this->starCount++;
			$eventlog->addGalaxyEvent("A new star has been created at $x,$y.");
		}
	}
	public function assignFirstStar($playerid,$playertype) {
		if (!is_numeric($playerid) || $playerid < 0) return -1;
		$result = $this->dbconn->query("SELECT starID FROM Stars s LEFT OUTER JOIN Players p ON s.playerID=p.playerID
			WHERE p.playerID is null AND starColor IN ('OR','YG','RD');");
		if ($result!==false) {
			$list = array();
			while($row=$result->fetch_assoc()) {
				$list[] = $row['starID'];
			}
			shuffle($list);
			if (count($list)===0) {
				$this->addStars(10);
				return $this->assignFirstStar($playerid,$playertype);
			}
			$star = new Star($this->dbconn,$list[0]);
			$star->setOwnerID($playerid,$playertype);
			$star->save();
			// Set player ID on Heliosphere Object
			$ho = new Heliosphere($this->dbconn);
			$ho->loadStar($star->getID());
			$ho->setPlayerID($playerid);
			$ho->save();
			// $ho->setPlayerIDonChildren();
			// Assign player to Star's habitable planet.
			$planetq = "SELECT HeliosphereObject FROM HeliosphereObjects WHERE heliosphereObjectType=3 AND starID=? AND habitable='Y';";
			$planets = $this->dbconn->prepare($planetq);
			$planets->bind_param("i",$id);
			$id = $star->getID();
			if ($planets->execute()) {
				$planets->bind_result($planetID);
				$planets->fetch();
				$planets->close();
				$planetManager = new PlanetManager($this->dbconn);
				$planetManager->stakeAClaim($planetID,$playerid,-1,true);
			} else	$planets->close();
			return $list[0];
		} else return -1;
	}
	public function listStars(int $starID=-1, int $playerID=-1) {
		$this->starArray = [];
		$q =
		"SELECT s.starID,s.starRandomName,s.starAssignedName,s.locationX,s.locationY,s.radius,s.starColor,s.playerID,
			pl.firstName,pl.lastName,pl.playerType,ho.habitable,ho.temperature,ho.population,
			(SELECT COUNT(1) FROM HeliosphereObjects hp WHERE hp.starID=s.starID AND hp.heliosphereObjectType=3) AS planets
			FROM Stars s
			LEFT OUTER JOIN Players pl ON s.playerID=pl.playerID
			JOIN HeliosphereObjects ho ON s.starID=ho.starID and ho.heliosphereObjectType=2";
		if ($starID > 0) $q .= " WHERE s.starID=$starID;";
		elseif ($playerID > 0) $q .= " WHERE s.playerID=$playerID;";
		else $q .= ";";
		$result = $this->dbconn->query($q);
		if ($result!=false) {
			$html = '<TABLE id="StarList">';
			$html .= '<TR><TH>Star ID</TH><TH>Star Random Name</TH><TH>Star Assigned Name</TH><TH>Location</TH><TH>Radius</TH><TH>Temperature</TH><TH>Star Type</TH>
				<TH>Habitable</TH><TH>Population</TH><TH># Planets</TH><TH>Owned by</TH></TR>';
			while ($row=$result->fetch_assoc()) {
				$this->starIDs[] = $row['starID'];
				$html .= "<TR id=\"starData{$row['starID']}\">";
				$html .= "<TD onClick=\"onClickStarID({$row['starID']});\">{$row['starID']}</TD>";
				$html .= "<TD onClick=\"onClickStarID({$row['starID']});\">{$row['starRandomName']}</TD>";
				$html .= "<TD onClick=\"onClickStarID({$row['starID']});\">{$row['starAssignedName']}</TD>";
				$html .= "<TD>{$row['locationX']}, {$row['locationY']}</TD>";
				$html .= "<TD>{$row['radius']} A.U.</TD>";
				$html .= "<TD>{$row['temperature']} K</TD>";
				$html .= "<TD>".Star::getColorName($row['starColor'])."</TD>";
				$html .= "<TD>{$row['habitable']}</TD>";
				$html .= "<TD>{$row['population']}</TD>";
				$html .= "<TD onClick=\"onClickStarID({$row['starID']});\">{$row['planets']}</TD>";
				$html .= "<TD";
				$playerID = $row['playerID'];
				if ($playerID < 1) {
					$html .= ">Unowned";
				} else {
					$html .= " onClick=\"onClickPlayerID({$row['playerID']});\">";
					if ($row['playerType']=='C') $html .= 'Computer player ';
					elseif ($row['playerType']=='H') $html .= 'Human player ';
					$html .= $row['firstName'].' '.$row['lastName'];
					$html .= " (ID {$row['playerID']})";
				}
				$html .= "</TD>";
				$html .= "</TR>";
			}
			$html .= "</TABLE>";
			return $html;
		}
		return $this->dbconn->error;
	}
}
?>
