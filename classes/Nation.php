<?php
class Nation {
	private $dbconn = null;
	private $nationID = -1;
	private $playerID = -1;
	private $hoID = -1;
	private $nationName = '';
	private $population = -1;

	public function __construct($conn, $nation=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		if ($nation > -1) {
			$q = "SELECT playerID,heliosphereObjectID,nationName,population FROM Nations WHERE nationID=?";
			$stmt = $this->dbconn->prepare($q);
			$stmt->bind_param("i",$nation);
			$result = $stmt->execute();
			if ($result!==false) {
				$stmt->bind_result($this->playerID,$this->hoID,$this->nationName,$this->population);
				$stmt->fetch();
				$stmt->close();
			}
			$this->nationID = $nation;
		}
	}
	public function makeANewNation($player, $ho, $name='', $maxpop=0, $setpop=0) {
		if (is_null($this->dbconn)) return -1;
		if (!is_numeric($player)) return -1;
		if (!is_numeric($ho)) return -1;
		$pop = $setpop;
		if ($pop==0) {
			if ($maxpop > 0) $pop = rand(floor($maxpop/4),$maxpop);
			else $pop = rand(10000,100000000)*10;	// Each nation starts with a population of between 100K and 10B.  Use the 10 multiplier to avoid libc errors on 32-bit systems.
		}
		$this->population = $pop;
		$q = "INSERT INTO Nations (playerID,heliosphereObjectID,nationName,population) VALUES (?,?,?,?);";
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("iisd",$player,$ho,$name,$pop);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->nationID = $this->dbconn->insert_id;
			$this->playerID = $player;
			$this->hoID = $ho;
			$this->nationName = $name;
		}
		return $this->nationID;
	}
	public function save() {
		if ($this->nationID < 1) $this->makeANewNation($this->playerID,$this->hoID,$this->nationName,0,$this->population);
		else {
			$q = 'UPDATE Nations SET playerID=?,heliosphereObjectID=?,nationName=?,population=? WHERE nationID=?;';
			$stmt = $this->dbconn->prepare($q);
			$stmt->bind_param("iisdi",$this->playerID,$this->hoID,$this->nationName,$this->population,$this->nationID);
			$result = $stmt->execute();
		}
	}
	public function getNationID() {
		return $this->nationID;
	}
	public function getPlayerID() {
		return $this->playerID;
	}
	public function getHOID() {
		return $this->hoID;
	}
	public function getNationName() {
		return $this->nationName;
	}
	public function getPopulation() {
		return $this->population;
	}
	public function setPlayerID($player) {
		if (is_integer($player) && $player > 0) $this->playerID = $player;
	}
	public function setNationName($name) {
		$this->nationName = $this->dbconn->real_escape_string($name);
	}
	public function changePopulationByPercent($pct) {
		// $pct should be in decimal format, positive for increase, negative for decrease
		if (!is_numeric($pct)) return;
		$delta = $this->population * $pct;
		$this->population += $delta;
	}
}
?>