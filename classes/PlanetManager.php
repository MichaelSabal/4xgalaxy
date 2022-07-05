<?php
// PlanetManager also manages Nations
class PlanetManager {
	private $dbconn = null;
	public function __construct($conn) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
	}
	public function stakeAClaim($planetID, $playerID, $population, $first=false) {
		// This function can be used to claim any heliosphereObject.
		$planeto = new Heliosphere($this->dbconn,$planetID);
		$na = new Nation($this->dbconn);
		$planetname = $planeto->getAssignedName();
		if (strlen($planetname)==0) $planetname = $planeto->getRandomName();
		if ($population >= 0) {
			$na->makeANewNation($playerID,$planetID,$planetname.'N1',$population,$population);
			$planeto->setPopulation($population);
		} else {
			$na->makeANewNation($playerID,$planetID,$planetname.'N1');
			$planeto->setPopulation($na->getPopulation());
		}
		$planeto->setPlayerID($playerID);
		$planeto->save();
		// TODO: Assign resources and technologies
	}
	public function listObjects($starID) {
		if (!is_numeric($starID) || $starID<1) return;
		$sm = new StarManager($this->dbconn);
		$sm->listStars($starID);
		echo "<BR /><BR />";
		$q = "SELECT ho.heliosphereObjectType,ho.heliosphereObjectRandomName,ho.apogee,ho.perigee,ho.radius,ho.period,ho.theta,ho.temperature,
			ho.playerID,ho.surfaceType,ho.imageFile,ho.habitable,ho.population,ho.heliosphereObject, 
			pl.firstName,pl.lastName,pl.playerType,st.surfaceTypeName,ht.heliosphereObjectTypeDescription 
			FROM heliosphereObjects ho
			LEFT OUTER JOIN HeliosphereObjectTypes ht ON ho.heliosphereObjectType=ht.heliosphereObjectType
			LEFT OUTER JOIN Players pl ON ho.playerID=pl.playerID 
			LEFT OUTER JOIN SurfaceTypes st ON ho.surfaceType=st.surfaceType 
			WHERE ho.starID=$starID AND ho.heliosphereObjectType > 2
			ORDER BY ho.apogee;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			echo '<TABLE id="StarSystemList">';
			echo '<TR><TH>Apogee</TH><TH>Perigee</TH><TH>Type</TH><TH>Surface</TH><TH>Temperature</TH><TH>Random Name</TH><TH>Radius</TH><TH>Period</TH><TH>Theta</TH>'.
				'<TH>Habitable</TH><TH>Population</TH><TH>Owned By</TH></TR>';
			while ($row=$result->fetch_assoc()) {
				echo "<TR onClick=\"onClickHOID($starID,{$row['heliosphereObject']});\">";
				$apogee = number_format($row['apogee'],2,'.',',');
				$perigee = number_format($row['perigee'],2,'.',',');
				$radius = number_format($row['radius'],2,'.',',');
				echo "<TD>$apogee A.U.</TD>";
				echo "<TD>$perigee A.U.</TD>";
				echo "<TD>{$row['heliosphereObjectTypeDescription']}</TD>";
				echo "<TD>{$row['surfaceTypeName']}</TD>";
				echo "<TD>{$row['temperature']} K</TD>";
				echo "<TD>{$row['heliosphereObjectRandomName']}</TD>";
				echo "<TD>$radius km</TD>";
				echo "<TD>{$row['period']} Y</TD>";
				echo "<TD>{$row['theta']} deg</TD>";
				echo "<TD>{$row['habitable']}</TD>";
				echo '<TD>'.number_format($row['population']).'</TD>';
				$playerID = $row['playerID'];
				echo "<TD>";
				if ($playerID < 1) {
					echo "Unowned";
				} else {
					if ($row['playerType']=='C') echo 'Computer player ';
					elseif ($row['playerType']=='H') echo 'Human player ';
					echo $row['firstName'].' '.$row['lastName'];
					echo " (ID {$row['playerID']})";
				}
				echo "</TD>";
				echo "</TR>";
			}
			echo '</TABLE>';
		}
	}
	private function getNationQuery() {
		return 'SELECT n.nationID,n.playerID,n.heliosphereObjectID,n.nationName,n.population,p.playerType,p.firstName,p.lastName,
			s.starID,coalesce(s.starAssignedName,s.starRandomName) as starName,
			coalesce(ho.heliosphereObjectAssignedName,ho.heliosphereObjectRandomName) as hoName,
			hot.heliosphereObjectTypeDescription 
			FROM Nations n 
			JOIN HeliosphereObjects ho ON n.heliosphereObjectID=ho.heliosphereObject 
			JOIN HeliosphereObjectTypes hot ON ho.heliosphereObjectType=hot.heliosphereObjectType 
			JOIN Stars s ON ho.starID=s.starID 
			JOIN Players p ON n.playerID=p.playerID ';
	}
	private function listNations($result) {
		echo '<TABLE id="nationList">';
		echo '<TR><TH>Nation ID</TH><TH>Nation Name</TH><TH>Population</TH><TH>Player</TH><TH>Location</TH></TR>';
		while ($row=$result->fetch_assoc()) {
			echo '<TR>';
			echo "<TD>{$row['nationID']}</TD>";
			echo "<TD>{$row['nationName']}</TD>";
			echo '<TD>'.number_format($row['population']).'</TD>';
			if ($row['playerType']=='H') $p = 'Human player <BR />';
			elseif ($row['playerType']=='C') $p = 'Computer player <BR />';
			else $p = 'H4X0R <BR />';
			$p .= $row['firstName'].' '.$row['lastName'].' ('.$row['playerID'].')';
			echo "<TD onClick=\"onClickPlayerID({$row['playerID']});\">$p</TD>";
			$l = 'Star '.$row['starName'].' ('.$row['starID'].') <BR />';
			$l .= $row['heliosphereObjectTypeDescription'].' '.$row['hoName'].' ('.$row['heliosphereObjectID'].')';
			echo "<TD>$l</TD>";
			echo '</TR>';
			// TODO: List Resources
			// TODO: List Technologies
		}
		echo '</TABLE>';
	}
	private function listNationsForPlayer3($playerID) {
		if (!is_numeric($playerID)) return;
		$q = $this->getNationQuery()."WHERE n.playerID=$playerID;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			$this->listNations($result);
		}
	}
	public function listNationsForPlayer($playerID) {
		if (!function_exists('mysqli_stmt_get_result')) {
			listNationsForPlayer3($playerID);
			return;
		}
		$q = $this->getNationQuery().'WHERE n.playerID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("i",$playerID);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->listNations($stmt->get_result());
		}
	}
	private function listNationsForHO3($hoid) {
		if (!is_numeric($hoid)) return;
		$q = $this->getNationQuery()."WHERE n.heliosphereObjectID=$hoid;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			$this->listNations($result);
		}
	}
	public function listNationsForHO($hoid) {
		if (!function_exists('mysqli_stmt_get_result')) {
			listNationsForHO3($hoid);
			return;
		}
		$q = $this->getNationQuery().'WHERE n.heliosphereObjectID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("i",$hoid);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->listNations($stmt->get_result());
		}
	}
}
?>