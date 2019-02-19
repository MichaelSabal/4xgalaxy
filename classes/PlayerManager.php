<?php
class PlayerManager {
	private $dbconn = null;
	private $playerList = array();
	private $playerCount = 0;
	public function __construct($conn) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		$this->countPlayers();
		if ($this->playerCount==0) $this->addComputerPlayer();
	}
	public function countPlayers() {
		$result = $this->dbconn->query('SELECT COUNT(1) as cnt FROM Players;');
		if ($result!==false) {
			$row = $result->fetch_assoc();
			$this->playerCount = $row['cnt'];
			return $row['cnt'];
		} else return -1;
	}
	public function countHumanPlayers() {
		$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Players WHERE playerType IN ('A','M','H');");
		if ($result!==false) {
			$row = $result->fetch_assoc();
			return $row['cnt'];
		} else return -1;
	}
	public function countComputerPlayers() {
		$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Players WHERE playerType IN ('C');");
		if ($result!==false) {
			$row = $result->fetch_assoc();
			return $row['cnt'];
		} else return -1;
	}
	public function countExpiredPlayers() {
		$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Players WHERE lastLogin < DATE_SUB(CURRENT_DATE(),INTERVAL 6 MONTH);");
		if ($result!==false) {
			$row = $result->fetch_assoc();
			return $row['cnt'];
		} else return -1;
	}
	public function countAlmostExpiredPlayers() {
		$result = $this->dbconn->query("SELECT COUNT(1) as cnt FROM Players 
			WHERE lastLogin BETWEEN DATE_SUB(CURRENT_DATE(),INTERVAL 6 MONTH) 
			AND DATE_SUB(CURRENT_DATE(),INTERVAL 3 MONTH);");
		if ($result!==false) {
			$row = $result->fetch_assoc();
			return $row['cnt'];
		} else return -1;		
	}
	public function addHumanPlayer() {
		// assumes $_POST contains the necessary information
		
	}
	public function addComputerPlayer() {
		$ai = new Player($this->dbconn);
		$ai->setType('C');
		$ai->setDateJoined();
		$ai->setLastLogin();
		$ai->setBirthday(new DateTime());
		$id = $ai->save();
		if ($id > 0) {
			$starmgr = new StarManager($this->dbconn);
			$starid = $starmgr->assignFirstStar($id,'C');
			if ($starid>0) {
				$ai->setFirstStar($starid);
				$ai->save();
			}
		}
	}
	public function listPlayers() {
		$result = $this->dbconn->query("SELECT playerID, playerType, firstName, lastName, birthday, 
			email, alias, ipAddress4, ipAddress6, firstStar, dateJoined, lastLogin,
			(SELECT COUNT(1) FROM Nations n WHERE n.playerID=p.playerID) as nationCount 
			FROM Players p;");
		if ($result!==false) {
			echo '<TABLE id="PlayerList">';
			echo '<TR><TH>ID</TH><TH>Type</TH><TH>Name</TH><TH>Birthday</TH><TH>Email</TH><TH>Alias</TH><TH>IP</TH>
			<TH>First Star</TH><TH>Date Joined</TH><TH>Last Login</TH><TH>Nations being managed</TH></TR>';
			while ($row=$result->fetch_assoc()) {
				echo "<TR onClick=\"onClickPlayerID({$row['playerID']});\">";
				echo "<TD>{$row['playerID']}</TD>";
				if ($row['playerType']=='H') echo '<TD>Human player</TD>';
				elseif ($row['playerType']=='C') echo '<TD>Computer player</TD>';
				echo "<TD>{$row['firstName']} {$row['lastName']}</TD>";
				$birthday = $row['birthday'];
				if (strlen($birthday)>10) $birthday = substr($birthday,0,10);
				echo "<TD>$birthday</TD>";
				echo "<TD>{$row['email']}</TD>";
				echo "<TD>{$row['alias']}</TD>";
				echo "<TD>{$row['ipAddress4']}<BR />{$row['ipAddress6']}</TD>";
				echo "<TD>{$row['firstStar']}</TD>";
				$dateJoined = $row['dateJoined'];
				if (strlen($dateJoined)>10) $dateJoined = substr($dateJoined,0,10);
				echo "<TD>$dateJoined</TD>";
				$lastLogin = $row['lastLogin'];
				if (strlen($lastLogin)>11) $lastLogin = substr($lastLogin,0,10).'<BR />'.substr($lastLogin,11);
				echo "<TD>$lastLogin</TD>";
				echo "<TD style=\"text-align: right;\">{$row['nationCount']}</TD>";
				echo '</TR>';
			}
			echo '</TABLE>';
		}
	}

}
?>