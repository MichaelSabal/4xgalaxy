<?php
/*
http://en.wikipedia.org/wiki/Stellar_classification#Spectral_types
Class O = Blue Supergiant (UV) - uninhabitable (1 in 3,000,000)
Class B = Blue Giant (BG) - uninhabitable (1 in 800)
Class A = White Hydrogen (WH) - uninhabitable (1 in 160)
Class F = White Calcium (WC) - uninhabitable (1 in 33)
Class G = Yellow Giant (YG) - habitable (1 in 13)
Class K = Orange Dwarf (OR) - habitable (1 in 8)
Class M = Red dwarf (RD) - habitable (3 in 8)
Class M = Red giant (RG) - uninhabitable (3 in 8)
*/
class Star {
	private $dbconn = null;
	private $starID = -1;
	private $starRandomName = '';
	private $owned = false;
	private $ownedByHuman = false;
	private $ownedByComputer = false;
	private $ownerID = -1;
	private $starAssignedName = '';
	private $location = array(-1,-1);
	private $radius = -1;
	private $color = '';
	private $validColors = '/UV/BG/WH/WC/YG/OR/RD/RG/';
	private $habitableColors = '/OR/YG/RD/';
	private $inhabitable = false;
	private $hoID = -1;
	private $children = array();
	private $goldilocksMin = 113.92;
	private $goldilocksMax = 236.44;
	public function __construct($conn,$id=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		if (is_numeric($id) && $id > 0) {
			$this->starID = $id;
			if (!function_exists('mysqli_stmt_get_result')) {
				$result = $this->dbconn->query("SELECT starID,starRandomName,coalesce(s.playerID,-1) as playerID,
					starAssignedName,locationX,
					locationY,radius,starColor FROM Stars s WHERE s.starID=$id;");
			} else {
				$q = "SELECT starID,starRandomName,coalesce(s.playerID,-1) as playerID,starAssignedName,locationX,
					locationY,radius,starColor FROM Stars s WHERE s.starID=?;";
				$stmt = $this->dbconn->prepare($q);	
				$stmt->bind_param('i',$id);
				$result = $stmt->execute();
				if ($result!==false) $result = $stmt->get_result();
			}
			if (!($result===false)) {
				$row = $result->fetch_assoc();
				$this->starRandomName = $row['starRandomName'];
				$this->ownerID = $row['playerID'];
				if ($this->ownerID > 0) $this->owned = true;
				$this->starAssignedName = $row['starAssignedName'];
				$this->location = array($row['locationX'],$row['locationY']);
				$this->radius = $row['radius'];
				$this->color = $row['starColor'];
				if (stripos($this->habitableColors,'/'.$this->color.'/')!==false) $this->inhabitable = true;
				else $this->inhabitable = false;
			}
		}
	}
	private function insert() {
		$q = "INSERT INTO Stars (starRandomName,locationX,locationY,radius,starColor) 
			VALUES (?,?,?,?,?);";
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('sddds',$this->starRandomName,$this->location[0],
			$this->location[1],$this->radius,$this->color);
		$result = $stmt->execute();
		if ($result!==false && $this->dbconn->affected_rows > 0)
			$this->starID = $this->dbconn->insert_id;
		// TODO: Also add a record to HeliosphereObjects
		$ho = new Heliosphere($this->dbconn);
		$ho->setStarID($this->starID);
		$ho->setParentID(1);	// All stars are children of the galaxy, HO=1.
		$ho->setObjectType(2);	// TODO: Resolve this from the database
		$ho->setPeriod(0);
		$ho->setRadius($this->radius * 149597870.691); // Star radius is in AU, HO radius is in km.
		$ho->setPlayerID($this->ownerID);
		$ho->setRandomName($this->starRandomName);
		$ho->setAssignedName($this->starAssignedName);
		$ho->setHabitable($this->inhabitable);
		$this->hoID = $ho->save();
		// TODO: Also add star system objects like planets, moons, asteroids, comets, artifacts
		$this->createPlanets();
	}
	private function update() {
		$q = 'UPDATE Stars SET 
			starRandomName=?,
			locationX=?,
			locationY=?,
			radius=?,
			starColor=?,
			playerID=?,
			starAssignedName=?
			WHERE starID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('sdddsisi',$this->starRandomName,$this->location[0],
			$this->location[1],$this->radius,$this->color,$this->ownerID,$this->starAssignedName,
			$this->starID);
		$result = $stmt->execute();
		if ($result!==false && $this->dbconn->affected_rows > 0)
			return true;
		else
			return false;
		// TODO: Also update HeliosphereObjects
		
	}
	public function save() {
		if ($this->starID<1) $this->insert();
		else $this->update();
	}
	public function createPlanets() {
		// Step 1: Determine how many planets are associated with the star
		$numPlanets = rand(0,15);
		if ($this->inhabitable && $numPlanets==0) $numPlanets=2;
		$sortedPlanets = array();
		for ($i=0;$i<$numPlanets;$i++) {
			// Step 2: For each planet, place it at some random distance from the star
			$d = rand(10,(125000/$this->radius));	// $d is a multiplier of the star's radius.  125K represents a shade less than 2 light-years.
			$this->children[$i] = new Heliosphere($this->dbconn);
			$this->children[$i]->setObjectType(3);
			$this->children[$i]->setStarID($this->starID);
			$this->children[$i]->setParentID($this->hoID);
			$this->children[$i]->setPlayerID(-1);	// Nobody owns newly created planets
			$au = 1.00*$d*$this->radius;
			if ($au < 0.0001 || $au > 125000) $au = rand(1,1250000)/100;
			$this->children[$i]->setApogee($au);
			$this->children[$i]->setPerigee($au);	// For early versions of the game, we'll only worry about circular orbits.
			$this->children[$i]->setRandomName('S'.$this->starID.'P'.$i);
			$sortedPlanets[$au]=$i;
		}
		// Step 3: Sort the planets by distance
		ksort($sortedPlanets);
		$lastd = 0;
		foreach ($sortedPlanets as $d=>$i) {
			// Step 4: If any planets are too close together, move them
			// Minimum distance is 0.25 AU.
			if ($d-$lastd < 0.25) {
				$newd = $d;
				if ($lastd > $newd) $newd = $lastd + 0.1;	// Avoid cumulative errors
				$newd += 0.3;
				$this->children[$i]->setApogee($newd);
				$this->children[$i]->setPerigee($newd);	
				$lastd = $newd;
			} else
				$lastd = $d;
		}
		// Re-sort
		unset($sortedPlanets);
		$sortedPlanets = array();
		for ($i=0;$i<$numPlanets;$i++) {
			$sortedPlanets[$this->children[$i]->getApogee()] = $i;
		}
		ksort($sortedPlanets);
		// Step 5: If this is a habitable star, and no planets exist within the habitable zone, create one.
		if ($this->inhabitable) {
			foreach ($sortedPlanets as $d=>$i) {
				if ($d > ($this->radius*$this->goldilocksMax)) {
					$this->createHabitablePlanet($numPlanets);
					$sortedPlanets[($this->children[$numPlanets]->getApogee())] = $numPlanets;
					// print "Creating Habitable Planet for Star {$this->starID} at ".$this->children[$numPlanets]->getApogee()."<BR />";
					$numPlanets++;
					break;
				}
				if (($d >= ($this->goldilocksMin*$this->radius)) && ($d <= ($this->goldilocksMax*$this->radius))) {
					$this->children[$i]->setHabitable(true);
					break;
				}
			}
		}
		// Step 6: Determine the planet type based on its distance from the star.
		// Step 7: Determine the planet radius based on its distance and its type. (Earth's average radius is 6371km)
		// TODO: Step 8: Set the planet's temperature based on its distance and its type.
		foreach ($sortedPlanets as $d=>$i) {
			if ($this->children[$i]->isHabitable()) {
				$o = rand(1,100);
				if ($o < 5) {
					$this->children[$i]->setSurfaceType('WAT');
				} elseif ($o < 25) {
					$this->children[$i]->setSurfaceType('ICE');
				} else {
					$this->children[$i]->setSurfaceType('ROK');			
				}
				$this->children[$i]->setRadius(rand(4400,20000));
			} elseif ($d < $this->goldilocksMin) {
				$o = rand(1,100);
				if ($o < 5) {
					$this->children[$i]->setSurfaceType('WAT');
					$this->children[$i]->setRadius(rand(2500,6000));
				} elseif ($o < 15) {
					$this->children[$i]->setSurfaceType('CRY');
					$this->children[$i]->setRadius(rand(500,4000));
				} elseif ($o < 25) {
					$this->children[$i]->setSurfaceType('LAV');
					$this->children[$i]->setRadius(rand(1500,5000));
				} else {
					$this->children[$i]->setSurfaceType('ROK');
					$this->children[$i]->setRadius(rand(3500,10000));
				}
			} elseif ($d < 5000) {
				$o = rand(1,100);
				if ($o < 3) {
					$this->children[$i]->setSurfaceType('CRY');
					$this->children[$i]->setRadius(rand(4400,20000));
				} elseif ($o < 15) {
					$this->children[$i]->setSurfaceType('ROK');
					$this->children[$i]->setRadius(rand(9000,40000));
				} elseif ($o < 20) {
					$this->children[$i]->setSurfaceType('ICE');
					$this->children[$i]->setRadius(rand(4400,20000));
				} else {
					$this->children[$i]->setSurfaceType('GAS');
					$this->children[$i]->setRadius(rand(35000,100000));
				}
			} else {
				$o = rand(1,1000);
				if ($o < 5) {
					$this->children[$i]->setSurfaceType('CRY');
					$this->children[$i]->setRadius(rand(100,2000));
				} elseif ($o < 100) {
					$this->children[$i]->setSurfaceType('ROK');
					$this->children[$i]->setRadius(rand(2000,20000));
				} elseif ($o < 550) {
					$this->children[$i]->setSurfaceType('GAS');
					$this->children[$i]->setRadius(rand(15000,50000));
				} else {
					$this->children[$i]->setSurfaceType('ICE');
					$this->children[$i]->setRadius(rand(500,5000));
				}
			}
			// Step 8: Determine the planet period based on distance
			$dir = rand(1,100);
			if ($dir < 25) $sign = -1; else $sign = 1;
			if (!$this->children[$i]->isHabitable())
				$this->children[$i]->setPeriod($sign*($d/rand(50,500)));
			else
				$this->children[$i]->setPeriod($sign*(5/rand(1,20)));
			$this->children[$i]->setTheta(rand(0,3600)/10);
//			if ($this->children[$i]->getRadius()>0 && $this->children[$i]->getApogee()>0)
				$this->children[$i]->save();
/*			elseif ($this->children[$i]->isHabitable()) {
				echo "Forcing apogee and radius.<BR />";
				$this->children[$i]->setRadius(6321);
				$this->children[$i]->setApogee($this->radius*$this->goldilocksMin*1.1);
				$this->children[$i]->setPerigee($this->radius*$this->goldilocksMin*1.1);
				$this->children[$i]->save();
			}
*/		}
		// Step 9: Create moons, using a similar process.
	}
	public function createHabitablePlanet($i) {
		// The "goldilocks" zone of a star is based on its luminosity.  To simplify this program,
		// we'll set the zone at between 113.92 and 236.44 times the radius.
		$d = 1.00*$this->radius*rand($this->goldilocksMin,$this->goldilocksMax);
		$this->children[$i] = new Heliosphere($this->dbconn);
		$this->children[$i]->setObjectType(3);
		$this->children[$i]->setPlayerID(-1);	// Nobody owns newly created planets
		$this->children[$i]->setStarID($this->starID);
		$this->children[$i]->setParentID($this->hoID);
		$this->children[$i]->setApogee($d);
		$this->children[$i]->setPerigee($d);	// For early versions of the game, we'll only worry about circular orbits.
		$this->children[$i]->setRandomName('S'.$this->starID.'P'.$i);
		$this->children[$i]->setHabitable(true);
	}
	public function setID($id) {
		if ($this->starID < 1 && is_numeric($id) && $id > 0) $this->starID = $id;
	}
	public function getID() {
		return $this->starID;
	}
	public function setRandomName($name) {
		$this->starRandomName = $this->dbconn->real_escape_string($name);
	}
	public function pickRandomName() {
		$len = rand(6,12);
		$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$numbers = '0123456789';
		if (strlen($this->color)==2) $rtn = $this->color;
		else {
			$rtn = $letters[rand(0,25)];
			$rtn .= $letters[rand(0,25)];
		}
		$rtn .= $letters[rand(0,25)];
		$rtn .= $numbers[rand(0,9)];
		$rtn .= $numbers[rand(0,9)];
		$rtn .= $numbers[rand(0,9)];
		$letters = strtolower($letters).$numbers;
		while (strlen($rtn) < $len) $rtn .= $letters[rand(0,35)];
		$this->starRandomName = $rtn;
		return $rtn;
	}
	public function getRandomName() {
		return $this->starRandomName;
	}
	public function setLocation($x,$y) {
		if (!is_numeric($x) || !is_numeric($y) || $x < 0 || $y < 0) return false;
		// this function will not validate whether a star is in the visible galaxy
		// nor will it validate whether the location is too close to another star.
		$this->location = array($x,$y);
	}
	public function getLocation() {
		return $this->location;
	}
	public function isOwned() {
		return $this->owned;
	}
	public function setOwnerID($id,$ownertype) {
		if (!is_numeric($id) || $id < 1) return;
		$this->ownerID = $id;
		$this->owned = true;
		if ($ownertype=='C') {
			$this->ownedByHuman = false;
			$this->ownedByComputer = true;
		} elseif (stripos('AMH',$ownertype)!==false) {
			$this->ownedByHuman = true;
			$this->ownedByComputer = false;
		}
	}
	public function setPlayerID($id,$ownertype) {
		$this->setOwnerID($id,$ownertype);
	}
	public function getOwnerID() {
		return $this->ownerID;
	}
	public function isHumanOwned() {
		return $this->ownedByHuman;
	}
	public function isComputerOwned() {
		return $this->ownedByComputer;
	}
	public function isHabitable() {
		return $this->inhabitable;
	}
	public function setRadius($r) {
		// The radius min/max values should be determined by the HeliosphereObjectType::Star record
		if (!is_numeric($r) || $r < 0.005 || $r > 5) return;
		$this->radius = $r;
	}
	public function pickRadius() {
		// Average star sizes in AU.
		// Earth's sun in approximately 0.009304812 AU in diameter.
		if ($this->color=='RG') $r = rand(300000,500000)/100000;
		if ($this->color=='UV') $r = rand(400000,500000)/100000;
		if ($this->color=='BG') $r = rand(300000,400000)/100000;
		if ($this->color=='WH') $r = rand(100000,300000)/100000;
		if ($this->color=='WC') $r = rand(50000,300000)/100000;
		if ($this->color=='YG') $r = rand(1000,500000)/1000000;
		if ($this->color=='OR') $r = rand(500,400000)/1000000;
		if ($this->color=='RD') $r = rand(100,300000)/1000000;
		$this->radius = $r;
	}
	public function getRadius() {
		return $this->radius;
	}
	public function setColor($c) {
		if (stripos($this->validColors,'/'.$c.'/')===false) return;
		$this->color = $c;
		if (stripos($this->habitableColors,'/'.$this->color.'/')!==false) $this->inhabitable = true;
		else $this->inhabitable = false;
	}
	public function getColor() {
		return $this->color;
	}
	public function pickColor() {
		$rate = mt_rand(1,3000000);
		$c = '';
		if ($rate==1) $c = 'UV';
		elseif ($rate<=375000) $c = 'OR';
		elseif ($rate<=1500000) $c = 'RD';
		elseif ($rate<=2625000) $c = 'RG';
		elseif ($rate<=2885770) $c = 'YG';
		elseif ($rate<=2976680) $c = 'WC';
		elseif ($rate<=2995430) $c = 'WH';
		else $c = 'BG';
		$this->setColor($c);
		return $c;
	}
	public static function getColorName($c) {
		switch (strtoupper($c)) {
			case 'UV':return 'Very rare blue supergiant'; break;
			case 'BG':return 'Blue giant'; break;
			case 'WH':return 'White hydrogen'; break;
			case 'WC':return 'White calcium'; break;
			case 'YG':return 'Yellow giant'; break;
			case 'OR':return 'Orange dwarf'; break;
			case 'RD':return 'Red dwarf'; break;
			case 'RG':return 'Red giant'; break;
		}
	}
}
?>