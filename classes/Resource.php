<?php
class Resource {
	/*
	ResourceSource may be 'P' for player created or 'S' for system created.
	Players may create their own resources and develop a backstory to convince other 
	players to purchase their creations.
	ResourceCreation may be 'N' for natural (consumes no other resources), or 'M' for manufactured (consumes other resources)
	TechnologyRequired is a composite field of TechnologyIDs that must be available on the planet for this resource to be gathered/made
	Population is how many people can be sustained with a single unit of this resource
	RenewalRate is how many units of a resource are added per year
	DecayRate is how many years before a resource returns to its raw materials (if manufactured) 
	or how many years before a resource becomes unusable (if natural).
	*/
	
	private $dbconn = null;
	private $resourceID = -1;
	private $approved = true;
	private $renewable = false;
	private $resourceName = '';
	private $resourceDescription = ''
	private $resourceTypeID = -1;
	private $resourceTypeDescription = '';
	private $resourcePrimaryUsage = '';
	private $resourceUsageDescription = '';
	private $resourceSource = 'S';
	private $playerID = -1;		// Who created this resource
	private $resourceCreation = '';
	private $population = -1;
	private $basicTimeToProduce = -1;
	private $technologyRequired = null;
	private $basicRenewalRate = -1;
	private $basicDecayRate = -1;
	private $columnList = 'approved,renewable,resourceName,resourceDescription,r.resourceTypeID,resourcePrimaryUsage,resourceSource,playerID,
			resourceCreation,population,basicTimeToProduce,technologyRequired,basicRenewalRate,basicDecayRate,rt.resourceTypeDescription,
			ru.resourceTypeUsageDescription';
	
	public function __construct($conn,$resource=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		if ($resource>0) {
			$this->resourceID = $resource;
			$this->load();
		}
	}
	public function load() {
		if ($this->resourceID<1) return;
		if (!function_exists('mysqli_stmt_get_result')) {
			if (!is_numeric($this->resourceID)) return;
			$result = $this->dbconn->query("SELECT {$this->columnList} 
				FROM Resources r 
				JOIN ResourceTypes rt ON r.resourceTypeID=rt.resourceTypeID
				JOIN ResourceTypeUsages ru ON r.resourcePrimaryUsage=ru.resourceTypeUsageID
				WHERE ResourceID={$this->resourceID};");
		} else {
			$q = "SELECT {$this->columnList} 
				FROM Resources r 
				JOIN ResourceTypes rt ON r.resourceTypeID=rt.resourceTypeID
				JOIN ResourceTypeUsages ru ON r.resourcePrimaryUsage=ru.resourceTypeUsageID
				WHERE ResourceID=?;";
			$stmt = $this->dbconn->prepare($q);
			$stmt->bind_param("i",$this->resourceID);
			$result = $stmt->execute();
			if ($result!==false) $result = $stmt->get_result();
		}
		if ($result!==false) {
			$row = $result->fetch_assoc();
			if ($row['approved']=='Y') $this->approved=true; else $this->approved=false;
			if ($row['renewable']=='Y') $this->renewable=true; else $this->renewable=false;
			$this->resourceName=$row['resourceName'];
			$this->resourceDescription=$row['resourceDescription'];
			$this->resourceTypeID=$row['resourceTypeID'];
			$this->resourceTypeDescription=$row['resourceTypeDescription'];
			$this->resourcePrimaryUsage=$row['resourcePrimaryUsage'];
			$this->resourceUsageDescription=$row['resourceTypeUsageDescription'];
			$this->resourceSource=$row['resourceSource'];
			$this->playerID=$row['playerID'];
			$this->resourceCreation=$row['resourceCreation'];
			$this->population=$row['population'];
			$this->basicTimeToProduce=$row['basicTimeToProduce'];
			$this->basicRenewalRate=$row['basicRenewalRate'];
			$this->basicDecayRate=$row['basicDecayRate'];
			$tech = $row['technologyRequired'];
			if (strlen($tech)>2) {
				$this->technologyRequired=explode('|',substr($tech,1,-1));
			}
		}
	}
	private function insert() {
		$q = 'INSERT INTO Resources (approved,renewable,resourceName,resourceDescription,resourceTypeID,resourcePrimaryUsage,resourceSource,playerID,
			resourceCreation,population,basicTimeToProduce,technologyRequired,basicRenewalRate,basicDecayRate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("ssssissisddsdd",
			$this->approved?'Y':'N',
			$this->renewable?'Y':'N',
			$this->resourceName,
			$this->resourceDescription,
			$this->resourceTypeID,
			$this->resourcePrimaryUsage,
			$this->resourceSource,
			$this->playerID,
			$this->resourceCreation,
			$this->population,
			$this->basicTimeToProduce,
			'|'.implode('|',$this->technologyRequired).'|',
			$this->basicRenewalRate,
			$this->basicDecayRate
		);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->resourceID = $this->dbconn->insert_id;
		}
	}
	private function update() {
		$q = 'UPDATE Resources SET 
			approved=?,
			renewable=?,
			resourceName=?,
			resourceDescription=?,
			resourceTypeID=?,
			resourcePrimaryUsage=?,
			resourceSource=?,
			playerID=?,
			resourceCreation=?,
			population=?,
			basicTimeToProduce=?,
			technologyRequired=?,
			basicRenewalRate=?,
			basicDecayRate=?
			WHERE ResourceID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param("ssssissisddsddi",
			$this->approved?'Y':'N',
			$this->renewable?'Y':'N',
			$this->resourceName,
			$this->resourceDescription,
			$this->resourceTypeID,
			$this->resourcePrimaryUsage,
			$this->resourceSource,
			$this->playerID,
			$this->resourceCreation,
			$this->population,
			$this->basicTimeToProduce,
			'|'.implode('|',$this->technologyRequired).'|',
			$this->basicRenewalRate,
			$this->basicDecayRate,
			$this->resourceID
		);
		$result = $stmt->execute();
	}
	public function save() {
		if ($this->resourceID<1) $this->insert();
		else $this->update();
	}
	public function getResourceID() return $this->resourceID;
	public function getApproved() return $this->approved;
	public function getRenewable() return $this->renewable;
	public function getResourceName() return $this->resourceName;
	public function getResourceDescription() return $this->resourceDescription;
	public function getResourceTypeID() return $this->resourceTypeID;
	public function getResourceTypeDescription() return $this->resourceTypeDescription;
	public function getResourcePrimaryUsage() return $this->resourcePrimaryUsage;
	public function getResourceUsageDescription() return $this->resourceUsageDescription;
	public function getResourceSource() return $this->resourceSource;
	public function getPlayerID() return $this->playerID;
	public function getResourceCreation() return $this->resourceCreation;
	public function getPopulation() return $this->population;
	public function getBasicTimeToProduce() return $this->basicTimeToProduce;
	public function getTechnologyRequired() return $this->technologyRequired;
	public function getBasicRenewalRate() return $this->basicRenewalRate;
	public function getBasicDecayRate() return $this->basicDecayRate;
	public function setResourceID($id) {
		if (!is_integer($id) || $id < 1) return;
		$this->resourceID = $id;
	}
	public function setApproved($a) {
		if (is_bool($a)) $this->approved = $a;
		elseif ($a=='Y') $this->approved = true;
		elseif ($a=='N') $this->approved = false;
	}
	public function setRenewable($r) {
		if (is_bool($r)) $this->renewable = $r;
		elseif ($r=='Y') $this->renewable = true;
		elseif ($r=='N') $this->renewable = false;
	}
	public function setResourceName($rn) {
		$this->resourceName = $this->dbconn->real_escape_string($rn);
	}
	public function setResourceDescription($rd) {
		$this->resourceDescription = $this->dbconn->real_escape_string($rd);
	}
	public function setResourceTypeID($rt) {
		if (!is_integer($rt) || $rt < 1) return;
		$this->resourceTypeID = $rt;
		// TODO: set resourceTypeDescription here
	}
	public function setResourcePrimaryUsage($pu) {
		$this->resourcePrimaryUsage = $this->dbconn->real_escape_string($pu);
		// TODO: set resourceUsageDescription here
	}
	public function setResourceSource($rs) {
		if ($rs=='P' || $rs=='S') $this->resourceSource = $rs;
	}
	public function playerID($p) {
		if (!is_integer($p) || $p < 1) return;
		$this->playerID = $p;
	}
	public function setResourceCreation($rc) {
		if ($rc=='N' || $rc=='M') $this->resourceCreation = $rc;
	}
	public function setPopulation($p) {
		if (is_numeric($p)) $this->population = $p;
	}
	public function setBasicTimeToProduce($t) {
		if (is_numeric($t)) $this->basicTimeToProduce = $t;
	}
	public function setBasicRenewalRate($r) {
		if (is_numeric($r)) $this->baseRenewalRate = $r;
	}
	public function setBasicDecayRate($r) {
		if (is_numeric($r)) $this->basicDecayRate = $r;
	}
	public function addTechnologyRequired($t) {
		if (!is_integer($t) || $t < 1) return;
		if (array_search($t,$this->technologyRequired)===false) $this->technologyRequired[]=$t;
	}
	public function removeTechnologyRequired($t) {
		if (!is_integer($t) || $t < 1) return;
		$z = array_search($t,$this->technologyRequired);
		if (!($z===false)) unset($this->technologyRequired[$z]);
	}
}
?>