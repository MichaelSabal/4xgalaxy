<?php
class Player {
	private $dbconn = null;
	private $playerID = -1;
	private $playerType = '';
	private $email = '';
	private $alias = '';
	private $birthday = null;
	private $dateJoined = null;
	private $lastLogin = null;
	private $firstName = '';
	private $lastName = '';
	private $ipaddress4 = '';
	private $ipaddress6 = '';
	private $firstStar = -1;
	private $salt1 = '';
	private $salt2 = '';
	private $halfBakedSecret = '';
	private $columnList = 'playerType,email,alias,birthday,
				dateJoined,lastLogin,firstName,lastName,ipaddress4,ipaddress6,firstStar,
				salt1,salt2';
	public function __construct($conn,$id=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		if ($id > 0) {
			if (!function_exists('mysqli_stmt_get_result')) {
				if (!is_numeric($id)) return;
				$result = $this->dbconn->query("SELECT {$this->columnList} FROM Players WHERE PlayerID=$id;");
			} else {
				$stmt = $this->dbconn->prepare("SELECT {$this->columnList} 
					FROM Players WHERE playerID=?;");
				$stmt->bind_param('i',$id);
				$result = $stmt->execute();
				if ($result!==false) $result = $stmt->get_result();
			}
			if ($result!==false) {
				$row = $result->fetch_assoc();
				$this->playerID = $id;
				$this->playerType = $row['playerType'];
				$this->email = $row['email'];
				$this->alias = $row['alias'];
				$this->birthday = new DateTime($row['birthday']);
				$this->dateJoined = new DateTime($row['dateJoined']);
				$this->lastLogin = new DateTime($row['lastLogin']);
				$this->firstName = $row['firstName'];
				$this->lastName = $row['lastName'];
				$this->ipaddress4 = $row['ipaddress4'];
				$this->ipaddress6 = $row['ipaddress6'];
				$this->firstStar = $row['firstStar'];
				$this->salt1 = $row['salt1'];
				$this->salt2 = $row['salt2'];
			}
		}
	}
	private function insert() {
		$q = 'INSERT INTO Players (playerType,email,alias,birthday,dateJoined,lastLogin,
			firstName,lastName,ipaddress4,ipaddress6,firstStar,salt1,salt2,secret) 
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('ssssssssssisss',$this->playerType,$this->email,$this->alias,
			$this->birthday->format('Y-m-d'),$this->dateJoined->format('Y-m-d'),
			$this->lastLogin->format('Y-m-d H:i:s'),$this->firstName,$this->lastName,
			$this->ipaddress4,$this->ipaddress6,$this->firstStar,$this->salt1,$this->salt2,
			sha1($this->salt2.$this->halfBakedSecret));
		$result = $stmt->execute();
		if ($result!==false) {
			$this->playerID = $this->dbconn->insert_id;
			return $this->playerID;
		} else return false;
		// TODO: Add an event log entry
	}
	private function update() {
		// Note that passwords are not changed with this function.
		$q = 'UPDATE Players SET 
			playerType=?,
			email=?,
			alias=?,
			birthday=?,
			dateJoined=?,
			lastLogin=?,
			firstName=?,
			lastName=?,
			ipaddress4=?,
			ipaddress6=?,
			firstStar=?
			WHERE playerID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('ssssssssssii',$this->playerType,$this->email,$this->alias,
			$this->birthday->format('Y-m-d'),$this->dateJoined->format('Y-m-d'),
			$this->lastLogin->format('Y-m-d H:i:s'),$this->firstName,$this->lastName,
			$this->ipaddress4,$this->ipaddress6,$this->firstStar,$this->playerID);
		$result = $stmt->execute();
		if ($result!==false && $this->dbconn->affected_rows > 0)
			return true;
		else
			return false;
	}
	public function save() {
		if ($this->playerID<1) return $this->insert();
		else return $this->update();
	}
	public function updatePassword() {
	
	}
	public function setID($id) {
		if ($this->playerID < 1 && is_numeric($id) && $id > 0) $this->playerID = $id;
	}
	public function getID() {
		return $this->playerID;
	}
	public function setType($t) {
		if (!strpos('AMHC',strtoupper(substr($t,0,1)))) return;
		$this->playerType = strtoupper(substr($t,0,1));
	}
	public function getType() {
		return $this->playerType;
	}
	public function setEmail($e) {
		// Pattern from http://regexlib.com/REDetails.aspx?regexp_id=26
		$pattern = '/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (!preg_match($pattern,$e)) return;
		$this->email = $e;
	}
	public function getEmail() {
		return $this->email;
	}
	public function setAlias($a) {
		// TODO: Validate uniqueness
		$this->alias = $dbconn->real_escape_string($a);
	}
	public function getAlias() {
		return $this->alias;
	}
	public function setBirthday($bd) {
		if (get_class($bd)=='DateTime') $this->birthday = $bd;
	}
	public function getBirthday() {
		return $this->birthday;
	}
	public function setDateJoined($ts=null) {
		if (get_class($ts)=='DateTime') $this->dateJoined = $ts;
		elseif (is_null($ts)) $this->dateJoined = new DateTime();
	}
	public function getDateJoined() {
		return $this->dateJoined;
	}
	public function setLastLogin($ts=null) {
		if (get_class($ts)=='DateTime') $this->lastLogin = $ts;
		elseif (is_null($ts)) $this->lastLogin = new DateTime();
	}
	public function getLastLogin() {
		return $this->lastLogin;
	}
	public function setFirstName($n) {
		$this->firstName = $this->dbconn->real_escape_string($n);
	}
	public function getFirstName() {
		return $this->firstName;
	}
	public function setLastName($n) {
		$this->lastName = $this->dbconn->real_escape_string($n);
	}
	public function getLastName() {
		return $this->lastName;
	}
	public function setIpAddress4($n) {
		// Pattern from http://regexlib.com/REDetails.aspx?regexp_id=32
		$pattern = '/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/';
		if (!preg_match($pattern,$n)) return;
		$this->ipaddress4 = $n;
	}
	public function getIpAddress4() {
		return $this->ipaddress4;
	}
	public function setIpAddress6($n) {
		// Pattern from http://regexlib.com/REDetails.aspx?regexp_id=906
		$pattern = '/^(^(([0-9A-F]{1,4}(((:[0-9A-F]{1,4}){5}::[0-9A-F]{1,4})|((:[0-9A-F]{1,4}){4}::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,1})|((:[0-9A-F]{1,4}){3}::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,2})|((:[0-9A-F]{1,4}){2}::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,3})|(:[0-9A-F]{1,4}::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,4})|(::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,5})|(:[0-9A-F]{1,4}){7}))$|^(::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,6})$)|^::$)|^((([0-9A-F]{1,4}(((:[0-9A-F]{1,4}){3}::([0-9A-F]{1,4}){1})|((:[0-9A-F]{1,4}){2}::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,1})|((:[0-9A-F]{1,4}){1}::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,2})|(::[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,3})|((:[0-9A-F]{1,4}){0,5})))|([:]{2}[0-9A-F]{1,4}(:[0-9A-F]{1,4}){0,4})):|::)((25[0-5]|2[0-4][0-9]|[0-1]?[0-9]{0,2})\.){3}(25[0-5]|2[0-4][0-9]|[0-1]?[0-9]{0,2})$$/';
		if (!preg_match($pattern,$n)) return;
		$this->ipaddress6 = $n;
	}
	public function getIpAddress6() {
		return $this->ipaddress6;
	}
	public function setFirstStar($id) {
		// TODO: Validate against database
		if (!is_numeric($id) || $id < 1) return;
		$this->firstStar = $id;
	}
	public function getFirstStar() {
		return $this->firstStar;
	}
	public function setSalt1($s) {
		$this->salt1 = $this->dbconn->real_escape_string($s);
	}
	public function getSalt1() {
		return $this->salt1;
	}
	public function setSalt2($s) {
		$this->salt2 = $this->dbconn->real_escape_string($s);
	}
	public function getSalt2() {
		return $this->salt2;
	}
	public function setHalfBakedSecret($s) {
		$this->halfBakedSecret = $this->dbconn->real_escape_string($s);
	}
	// There is intentionally no function to getHalfBakedSecret.
}
?>