<?php
class Heliosphere {
	private $dbconn = null;
	private $hObjectID = -1;
	private $hObjectType = -1;
	private $hRandomName = '';
	private $hAssignedName = '';
	private $parentObject = -1;
	private $starID = -1;
	private $habitable = false;
	private $population = 0;
	private $apogee = 0;
	private $perigee = 0;
	private $radius = 0;
	private $period = 0;
	private $theta = 0;
	private $playerID = -1;
	private $temperature = 0;
	private $surfaceType = '';
	private $imageFile = '';
	private $columnList = "heliosphereObject,heliosphereObjectType,heliosphereObjectRandomName,heliosphereObjectAssignedName,
		parentObject,starID,habitable,population,apogee,perigee,radius,period,theta,playerID,temperature,surfaceType,imageFile";
	public function __construct($conn,$id=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		else {
			echo "conn is not a database. ".get_class($conn)."<BR />";
			return;
		}
		if (is_numeric($id) && $id > 0) {
			$this->hObjectID = $id;
			if (!function_exists('mysqli_stmt_get_result')) {
				$result = $this->dbconn->query("SELECT {$this->columnList}
					FROM HeliosphereObjects ho WHERE ho.HeliosphereObject=$id;");
			} else {
				$q = 'SELECT '.$this->columnList.' FROM HeliosphereObjects ho WHERE ho.heliosphereObject=?;';
				if (!($stmt = $this->dbconn->prepare($q))) echo "Could not prepare statement: $q<BR />";	
				$stmt->bind_param("i",$id);
				$result = $stmt->execute();
				if ($result!==false) $result = $stmt->get_result();
			}
			if (!($result===false)) {
				$row = $result->fetch_assoc();
				$this->hObjectType = $row['heliosphereObjectType'];
				$this->hRandomName = $row['heliosphereObjectRandomName'];
				$this->hAssignedName = $row['heliosphereObjectAssignedName'];
				$this->parentObject = $row['parentObject'];
				$this->starID = $row['starID'];
				if ($row['habitable']=='Y') $this->habitable=true;
				else $this->habitable=false;
				$this->population = $row['population'];
				$this->apogee = $row['apogee'];
				$this->perigee = $row['perigee'];
				$this->radius = $row['radius'];
				$this->period = $row['period'];
				$this->theta = $row['theta'];
				$this->playerID = $row['playerID'];
				$this->temperature = $row['temperature'];
				$this->surfaceType = $row['surfaceType'];
				$this->imageFile = $row['imageFile'];
			}
		}
	}
	public function loadStar($star) {
		if (is_numeric($star) && $star > 0) {
			if (!function_exists('mysqli_stmt_get_result')) {
				$result = $this->dbconn->query("SELECT ho.*
					FROM HeliosphereObjects ho 
					JOIN HeliosphereObjectTypes ot ON ho.heliosphereObjectType=ot.heliosphereObjectType
					AND ot.heliosphereObjectTypeDescription='Star'
					WHERE ho.starID=$star;");
			} else {
				$q = "SELECT ho.*
					FROM HeliosphereObjects ho 
					JOIN HeliosphereObjectTypes ot ON ho.heliosphereObjectType=ot.heliosphereObjectType
					AND ot.heliosphereObjectTypeDescription='Star'
					WHERE ho.starID=?;";
				$stmt = $this->dbconn->prepare($q);	
				$stmt->bind_param("i",$star);
				$result = $stmt->execute();
				if ($result!==false) $result = $stmt->get_result();
			}
			if (!($result===false)) {
				$row = $result->fetch_assoc();
				$this->hObjectID = $row['heliosphereObject'];
				$this->hObjectType = $row['heliosphereObjectType'];
				$this->hRandomName = $row['heliosphereObjectRandomName'];
				$this->hAssignedName = $row['heliosphereObjectAssignedName'];
				$this->parentObject = $row['parentObject'];
				$this->starID = $row['starID'];
				if ($row['habitable']=='Y') $this->habitable=true;
				else $this->habitable=false;
				$this->population = $row['population'];
				$this->apogee = $row['apogee'];
				$this->perigee = $row['perigee'];
				$this->radius = $row['radius'];
				$this->period = $row['period'];
				$this->theta = $row['theta'];
				$this->playerID = $row['playerID'];
				$this->temperature = $row['temperature'];
				$this->surfaceType = $row['surfaceType'];
				$this->imageFile = $row['imageFile'];
			}
		}
	}
	public function setPlayerIDonChildren($habitableOnly=true) {
		if ($this->starID<1 || $this->playerID<1) return;
		$q = "UPDATE HeliosphereObjects SET playerID=? WHERE starID=?";
		if ($habitableOnly) {
			$q=$q." AND habitable='Y';";
		} else $q=$q.";";
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("ii",$this->playerID,$this->starID);
		$stmt->execute();
	}
	private function insert() {
		$cl = substr($this->columnList,strpos($this->columnList,',')+1);
		$h = 'N';
		if ($this->habitable) $h = 'Y';
		$q = "INSERT INTO HeliosphereObjects ($cl) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("issiisidddddiiss",$this->hObjectType,$this->hRandomName,$this->hAssignedName,$this->parentObject,
			$this->starID,$h,$this->population,$this->apogee,$this->perigee,$this->radius,$this->period,$this->theta,$this->playerID,
			$this->temperature,$this->surfaceType,$this->imageFile);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->hObjectID = $this->dbconn->insert_id;
			return $this->hObjectID;
		} else return false;
	}
	private function update() {
		$q = "UPDATE HeliosphereObjects SET 
			heliosphereObjectType=?,
			heliosphereObjectRandomName=?,
			heliosphereObjectAssignedName=?,
			parentObject=?,
			starID=?,
			habitable=?,
			population=?,
			apogee=?,
			perigee=?,
			radius=?,
			period=?,
			theta=?,
			playerID=?,
			temperature=?,
			surfaceType=?,
			imageFile=? 
			WHERE heliosphereObject=?";
		$h = 'N';
		if ($this->habitable) $h = 'Y';
			$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("issiisidddddiissi",$this->hObjectType,$this->hRandomName,$this->hAssignedName,$this->parentObject,
			$this->starID,$h,$this->population,$this->apogee,$this->perigee,$this->radius,$this->period,$this->theta,$this->playerID,
			$this->temperature,$this->surfaceType,$this->imageFile,$this->hObjectID);
		$result = $stmt->execute();
		if ($result!==false && $this->dbconn->affected_rows > 0)
			return true;
		else
			return false;
	}
	public function save() {
		if ($this->hObjectID<1) return $this->insert();
		else return $this->update();
	}
	public function setHeliosphereID($id) {
		if ($this->hObjectID < 1 && is_numeric($id) && $id > 0) $this->hObjectID = $id;
	}
	public function getHeliosphereID() {
		return $this->hOjbectID;
	}
	public function setStarID($id) {
		if (is_numeric($id) && $id > 0) $this->starID = $id;
	}
	public function getStarID() {
		return $this->starID;
	}
	public function setParentID($id) {
		if (is_numeric($id) && $id > 0) $this->parentObject = $id;
	}
	public function getParentID() {
		return $this->parentObject;
	}
	public function setPlayerID($id) {
		if (is_numeric($id) && $id > 0) $this->playerID = $id;
	}
	public function getPlayerID() {
		return $this->playerID;
	}
	public function setObjectType($type) {
		// TODO: Validate against database
		if (is_numeric($type) && ($type > 0)) $this->hObjectType = $type;
	}
	public function getObjectType() {
		return $this->hObjectType;
	}
	public function setRandomName($name) {
		$this->hRandomName = $this->dbconn->real_escape_string($name);
	}
	public function getRandomName() {
		return $this->hRandomName;
	}
	public function setAssignedName($name) {
		$this->hAssignedName = $this->dbconn->real_escape_string($name);
	}
	public function getAssignedName() {
		return $this->hAssignedName;
	}
	public function setHabitable($h) {
		if (is_bool($h)) $this->habitable = $h;
	}
	public function isHabitable() {
		return $this->habitable;
	}
	public function setPopulation($p) {
		if (is_numeric($p) && $p >= 0) $this->population = $p;
	}
	public function getPopulation() {
		return $this->population;
	}
	public function setApogee($a) {
		if (is_numeric($a) && $a >= 0) $this->apogee = $a;
	}
	public function getApogee() {
		return $this->apogee;
	}
	public function setPerigee($p) {
		if (is_numeric($p) && $p >= 0) $this->perigee = $p;
	}
	public function getPerigee() {
		return $this->perigee;
	}
	public function setRadius($r) {
		if (is_numeric($r) && $r >= 0) $this->radius = $r;
	}
	public function getRadius() {
		return $this->radius;
	}
	public function setPeriod($p) {
		if (is_numeric($p)) $this->period = $p;	
		// Clockwise revolutions have positive periods,
		// Geostationary objects have zero periods,
		// Counterclockwise revolutions have negative periods.
	}
	public function getPeriod() {
		return $this->period;
	}
	public function setTheta($t) {
		if (is_numeric($t) && ($t > -360 && $t <= 360)) $this->theta = $t;
	}
	public function getTheta() {
		return $this->theta;
	}
	public function setImageFile($f) {
		if (file_exists($f)) $this->imageFile = $f;
	}
	public function getImageFile() {
		return $this->imageFile;
	}
	public function setTemperature($t) {
		if (is_numeric($t)) $this->temperature=floor($t);
	}
	public function getTemparature() {
		return $this->temperature;
	}
	public function setSurfaceType($st) {
		// TODO: Validate against database
		$this->surfaceType = $this->dbconn->real_escape_string($st);
	}
	public function getSurfaceType() {
		return $this->surfaceType;
	}
}
?>