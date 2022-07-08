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
	private $secret = '';
	private $columnList = 'playerType,email,alias,birthday,
				dateJoined,lastLogin,firstName,lastName,ipaddress4,ipaddress6,firstStar';
	public function __construct($conn,$id=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		if ($id > 0) {
			$this->load($id);
		}
	}
	private function load(int $id=-1) {
		if ($id < 1) return;
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
		}
	}
	private function insert(): bool {
		$q = 'INSERT INTO Players (playerType,email,alias,birthday,dateJoined,lastLogin,
			firstName,lastName,ipaddress4,ipaddress6,firstStar,secret)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?);';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('ssssssssssis',$p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,$p12);
		$p1 = $this->playerType;
		$p2 = $this->email;
		$p3 = $this->alias;
		$p4 = $this->birthday->format('Y-m-d');
		$p5 = $this->dateJoined->format('Y-m-d');
		$p6 = $this->lastLogin->format('Y-m-d H:i:s');
		$p7 = $this->firstName;
		$p8 = $this->lastName;
		$p9 = $this->ipaddress4;
		$p10 = $this->ipaddress6;
		$p11 = $this->firstStar;
		$p12 = password_hash($this->secret,PASSWORD_DEFAULT);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->playerID = $this->dbconn->insert_id;
			return true;
		} else return false;
		// TODO: Add an event log entry
	}
	private function update(): bool {
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
			firstStar=?,
			secret=?
			WHERE playerID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('ssssssssssisi',$this->playerType,$this->email,$this->alias,
			$this->birthday->format('Y-m-d'),$this->dateJoined->format('Y-m-d'),
			$this->lastLogin->format('Y-m-d H:i:s'),$this->firstName,$this->lastName,
			$this->ipaddress4,$this->ipaddress6,$this->firstStar,
			password_hash($this->secret,PASSWORD_DEFAULT),$this->playerID);
		$result = $stmt->execute();
		if ($result!==false && $this->dbconn->affected_rows > 0)
			return true;
		else
			return false;
	}
	public function save(): bool {
		if ($this->playerID<1) return $this->insert();
		else return $this->update();
	}
	public function updatePassword() {

	}
	public function setID(int $id=-1) {
		if ($this->playerID < 1 && is_numeric($id) && $id > 0) {
			$this->load($id);
		}
	}
	public function getID(): int {
		return $this->playerID;
	}
	public function setType(string $t) {
		if (!strpos('AMHC',strtoupper(substr($t,0,1)))) return;
		$this->playerType = strtoupper(substr($t,0,1));
	}
	public function getType() {
		return $this->playerType;
	}
	public function setEmail(string $e) {
		// Pattern from http://regexlib.com/REDetails.aspx?regexp_id=26
		$pattern = '/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (!preg_match($pattern,$e)) return;
		$this->email = $e;
	}
	public function getEmail(): string {
		return $this->email;
	}
	public function setAlias(string $a) {
		// TODO: Validate uniqueness
		$this->alias = $a;
	}
	public function getAlias() {
		return $this->alias;
	}
	public function setBirthday(DateTime $bd) {
		$this->birthday = $bd;
	}
	public function getBirthday(): DateTime {
		return $this->birthday;
	}
	public function setDateJoined($ts=null) {
		if ($ts instanceof DateTime) $this->dateJoined = $ts;
		elseif (is_null($ts)) $this->dateJoined = new DateTime();
	}
	public function getDateJoined() {
		return $this->dateJoined;
	}
	public function setLastLogin($ts=null) {
		if ($ts instanceof DateTime) $this->lastLogin = $ts;
		elseif (is_null($ts)) $this->lastLogin = new DateTime();
	}
	public function getLastLogin() {
		return $this->lastLogin;
	}
	public function setFirstName(string $n) {
		$this->firstName = $n;
	}
	public function getFirstName(): string {
		return $this->firstName;
	}
	public function setLastName(string $n) {
		$this->lastName = $n;
	}
	public function getLastName(): string {
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
	public function setSecret(string $secret) {
		$this->secret = $secret;
	}
	public function validateSecret(string $secret): bool {
		$rtn = false;
		$stmt = $this->dbconn->prepare("SELECT secret FROM Players WHERE playerID=?");
		if ($stmt!==false) {
			$stmt->bind_param('i',$id);
			$id = $this->playerID;
			$result = $stmt->execute();
			if ($result!==false) {
				$stmt->bind_result($storedsecret);
				if ($stmt->fetch() && password_verify($secret,$storedsecret)) $rtn = true;
			}
			$stmt->close();
		}
		return $rtn;
	}
	// There is intentionally no function to getHalfBakedSecret.
	public function lookupByEmail(string $email): int {
		$q = "SELECT playerId FROM Players WHERE email=?";
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('s',$p1);
		$p1 = $email;
		$result = $stmt->execute();
		if ($result===false) return -1;
		$stmt->bind_result($id);
		if (!$stmt->fetch()) {
			return -1;
		}
		$this->load($id);
		return $id;
	}
}
?>
