<?php
class Galaxy {
	private $dbconn = null;
	private $settings = null;
	private $defaultDayZero = 2000.00;
	private $minimumSeparationOfStars = 2;
	public function __construct($conn) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
		$q = "SELECT settingID,settingValue FROM GalaxySettings;";
		$result = $this->dbconn->query($q);
		$this->settings = array();
		if ($result!==false) {
			while ($row = $result->fetch_assoc()) {
				$this->settings[$row['settingID']] = $row['settingValue'];
			}
			$result->close();
		}
	}
	public function save() {
		$stmt = $this->dbconn->prepare('INSERT INTO GalaxySettings (settingID,settingValue) VALUES (?,?) '.
			'ON DUPLICATE KEY UPDATE settingValue = ?;');
		$stmt->bind_param('sss',$sid,$sval,$supval);
		foreach ($this->settings as $id=>$val) {
			$sid = $id;
			$sval = $val;
			$supval = $val;
			$stmt->execute();
		}
		$stmt->close();
	}
	public function getSize() {
		if (!isset($this->settings['GalaxyWidth']) || !isset($this->settings['GalaxyHeight'])) return array(0,0);
		else return array($this->settings['GalaxyWidth'],$this->settings['GalaxyHeight']);
	}
	public function growGalaxy($by) {
		if (!is_numeric($by)) return false;
		if (!isset($this->settings['GalaxyWidth'])) $this->settings['GalaxyWidth'] = 0;
		if (!isset($this->settings['GalaxyHeight'])) $this->settings['GalaxyHeight'] = 0;
		$this->settings['GalaxyWidth'] += $by;
		$this->settings['GalaxyHeight'] += $by;
		$w = $this->settings['GalaxyWidth'];
		$h = $this->settings['GalaxyHeight'];
		$ms = floor(2.5*(($w+$h)/2));
		if ($ms > $this->settings['MaxStars']) $this->settings['MaxStars'] = $ms;
		return true;
	}
	public function getMaxStars() {
		if (!isset($this->settings['MaxStars'])) return 0;
		return $this->settings['MaxStars'];
	}
	public function getVersion() {
		if (isset($this->settings['Version'])) return $this->settings['Version'];
		else return 'x.xxx';
	}
	public function getGameCalendar() {
		if (!isset($this->settings['GameCalendar'])) $this->settings['GameCalendar'] = $defaultDayZero;
		return $this->settings['GameCalendar'];
	}
	public function advanceGameCalendar($by) {
		if (!is_numeric($by)) return false;
		if ($by > 1000) return false;
		if (!isset($this->settings['GameCalendar'])) $this->settings['GameCalendar'] = $defaultDayZero;
		$this->settings['GameCalendar'] += $by;
	}
	public function getMinimumSeparationOfStars() {
		return $this->minimumSeparationOfStars;
	}
}
?>