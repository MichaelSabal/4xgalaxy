<?php
class Event {
	private $dbconn = null;
	private $eventID = -1;
	private $eventType = -1;
	private $eventTime = null;
	private $playerID = -1;
	private $eventDescription = '';
	public function __construct($conn,$id=-1) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		if ($id>0) {
			$stmt = $this->dbconn->prepare('SELECT eventType,eventTime,playerID,eventDescription FROM EventLog 
				WHERE eventID=?;');
			$stmt->bind_param('i',$id);
			$result = $stmt->execute();
			if ($result!==false) {
				$stmt->bind_result($this->eventType,$this->eventTime,$this->playerID,$this->eventDescription);
				$stmt->fetch();
				$stmt->close();
			}
			$this->eventID = $id;
		}
	}
	private function insert() {
		$q = 'INSERT INTO EventLog (eventType,eventTime,playerID,eventDescription) VALUES (?,?,?,?);';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('isis',$this->eventType,$this->eventTime->format('Y-m-d H:i:s'),$this->playerID,$this->eventDescription);
		$result = $stmt->execute();
		if ($result!==false) {
			$this->eventID = $this->dbconn->insert_id;
			return $this->eventID;
		} else return false;
	}
	private function update() {
		$q = 'UPDATE EventLog SET eventType=?, eventTime=?,playerID=?,eventDescription=? WHERE eventID=?;';
		$stmt = $this->dbconn->prepare($q);
		$stmt->bind_param('isisi',$this->eventType,$this->eventTime->format('Y-m-d H:i:s'),
			$this->playerID,$this->eventDescription,$this->eventID);
		$result = $stmt->execute();
		if ($result!==false && $this->dbconn->affected_rows > 0)
			return true;
		else
			return false;
	}
	public function save() {
		if ($this->eventID<1) return $this->insert();
		else return $this->update();
	}
	public function setEventID($id) {
		if (!is_numeric($id) || $id < 1 || $this->eventID>0) return;
		$this->eventID = $id;
	}
	public function getEventID() {
		return $this->eventID;
	}
	public function setEventType($et) {
		if (!is_numeric($et) || $et < 1 || $et > 8) return;
		$this->eventType = $et;
	}
	public function getEventType() {
		return $this->eventType;
	}
	public function getEventTypeDescription() {
		$stmt = $this->dbconn->prepare('SELECT eventTypeDescription FROM EventTypes WHERE eventType=?;');
		$stmt->bind_param('i',$this->eventType);
		$result = $stmt->execute();
		if ($result!==false) {
			$row = $result->fetch_assoc();
			return $row['eventTypeDescription'];
		} else return 'Invalid event type';
	}
	public function setEventTime($ts=null) {
		if (get_class($ts)=='DateTime') $this->eventTime = $ts;
		elseif (is_null($ts)) $this->eventTime = new DateTime();
	}
	public function getEventTime() {
		return $this->eventTime;
	}
	public function setPlayerID($id) {
		if (!is_numeric($id) || $id < 1) return;
		// TODO: Validate $id against the database
		$this->playerID = $id;
	}
	public function getPlayerID() {
		return $this->playerID;
	}
	public function setEventDescription($desc) {
		$this->eventDescription = $this->dbconn->real_escape_string($desc);
	}
	public function getEventDescription() {
		return $this->eventDescription;
	}
}
?>