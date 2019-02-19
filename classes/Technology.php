<?php
class Technology {
	private $dbconn = null;
	private $technologyID = -1;
	private $technologyTypeID = -1;
	private $technologyName = "";
	private $technologyLevel = -1;
	private $distanceLY = 0;
	private $percentFaster = 0;
	private $productionIncreasePercent = 0;
	private $costReductionPercent = 0;
	private $affectsResourceType = "";
	private $prerequisites = null;
	private $columnList = 'technologyTypeID,technologyName,technologyLevel,distanceLY,percentFaster,
			productionIncreasePercent,costReductionPercent,affectsResourceType,prerequisites';
	public function __construct($conn, $tid=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		if ($tid > 0) {
			$this->technologyID = $tid;
			$this->load();
		}
		else $this->prerequisites = array();
	}
	public function load() {
		if ($this->technologyID<1) return;
		if (!function_exists('mysqli_stmt_get_result')) {
			if (!is_numeric($this->technologyID)) return;
			$result = $this->dbconn->query("SELECT {$this->columnList} FROM Technologies WHERE technologyID={$this->technologyID};");
		} else {
			$q = "SELECT {$this->columnList} FROM Technologies WHERE technologyID=?;";
			$stmt = $this->dbconn->prepare($q);
			$stmt->bind_param("i",$this->technologyID);
			$result = $stmt->execute();
			if ($result!==false) $result = $stmt->get_result();
		}
		if ($result!==false) {
			$row = $result->fetch_assoc();
			$this->technologyTypeID = $row['technologyTypeID'];
			$this->technologyName = $row['technologyName'];
			$this->technologyLevel = $row['technologyLevel'];
			$this->distancyLY = $row['distanceLY'];
			$this->percentFaster = $row['percentFaster'];
			$this->productionIncreasePercent = $row['productionIncreasePercent'];
			$this->costReductionPercent = $row['costReductionPercent'];
			$this->affectsResourceType = $row['affectsResourceType'];
			$this->prerequisites = explode('|',substring($row['prerequisites'],1,-1));
		}
	}
	private function insert() {
		$q = 'INSERT INTO Technologies (technologyTypeID,technologyName,technologyLevel,distanceLY,
			percentFaster,productionIncreasePercent,costReductionPercent,affectsResourceType,prerequisites) 
			VALUES (?,?,?,?,?,?,?,?,?);';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("isiddddss",
			$this->technologyTypeID,
			$this->technologyName,
			$this->technologyLevel,
			$this->distanceLY,
			$this->percentFaster,
			$this->productionIncreasePercent,
			$this->costReductionPercent,
			$this->affectsResourceType,
			'|'.implode('|',$this->prerequisites).'|'
		);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->technologyID = $this->dbconn->insert_id;
		}
	}
	private function update() {
		$q = 'UPDATE Technologies SET 
			technologyTypeID=?,technologyName=?,technologyLevel=?,distanceLY=?,
			percentFaster=?,productionIncreasePercent=?,costReductionPercent=?,affectsResourceType=?,prerequisites=? 
			WHERE TechnologyID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("isiddddssi",
			$this->technologyTypeID,
			$this->technologyName,
			$this->technologyLevel,
			$this->distanceLY,
			$this->percentFaster,
			$this->productionIncreasePercent,
			$this->costReductionPercent,
			$this->affectsResourceType,
			'|'.implode('|',$this->prerequisites).'|',
			$this->technologyID
		);
		$result = $stmt->execute();
	}
	public function save() {
		if ($this->technologyID<1) $this->insert();
		else $this->update();
	}
	public function getTechnologyID() return $this->technologyID;
	public function getTechnologyTypeID() return $this->technologyTypeID;
	public function getTechnologyName() return $this->technologyName;
	public function getTechnologyLevel() return $this->technologyLevel;
	public function getDistancyLY() return $this->distanceLY;
	public function getPercentFaster() return $this->percentFaster;
	public function getProductionIncreasePercent() return $this->productionIncreasePercent;
	public function getCostReductionPercent() return $this->costReductionPercent;
	public function getAffectsResourceType() return $this->affectsResourceType;
	public function getPrerequisites() return $this->prerequisites;
	public function setTechnologyID($id) {
		if (is_integer($id) && $id > 0) $this->technologyID=$id;
	}
	public function setTechnologyTypeID($id) {
		if (is_integer($id) && $id > 0) $this->technologyTypeID=$id;
	}
	public function setTechnologyName($n) {
		$this->technologyName = $this->dbconn->real_escape_string($n);
	}
	public function setTechnologyLevel($l) {
		if (is_integer($l) && $l>=0) $this->technologyLevel = $l;
	}
	public function setDistanceLY($d) {
		if (is_numeric($d)) $this->distanceLY = $d;
	}
	public function setPercentFaster($pf) {
		if (is_numeric($pf)) $this->percentFaster = $pf;
	}
	public function setProductionIncreasePercent($pf) {
		if (is_numeric($pf)) $this->productionIncreasePercent = $pf;
	}
	public function setCostReductionPercent($pf) {
		if (is_numeric($pf)) $this->costReductionPercent($pf);
	}
	public function setAffectsResourceType($rt) {
		$this->affectsResourceType = $this->dbconn->real_escape_string($rt);
	}
	public function setPrerequisites($pr) {
		if (is_array($pr))
			$this->prerequisites = $pr;
	}
}
?>