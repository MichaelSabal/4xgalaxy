<?php
class EventLog {
	private $dbconn = null;
	
	public function __construct($conn) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
	}
	public function addEvent($msg,$eventType,$playerID=-1) {
		$ev = new Event($this->dbconn);
		$ev->setEventTime();
		$ev->setEventDescription($msg);
		$ev->setEventType($eventType);
		$ev->setPlayerID($playerID);
		$ev->save();
	}
	public function addPlayerEvent($msg,$playerID) {
		return $this->addEvent($msg,4,$playerID);
	}
	public function addGalaxyEvent($msg) {
		return $this->addEvent($msg,5);
	}
}
?>