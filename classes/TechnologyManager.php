<?php
class TechnologyManager {
	private $dbconn = null;
	public function __construct($conn) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
	}
	public function technologyTypeSelect() {
		$q = "SELECT technologyTypeID,technologyTypeDescription FROM TechnologyTypes;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			
		}
	}
	public function listAllTechnologies() {
		$q = "SELECT technologyID,technologyTypeID,technologyTypeDescription,technologyName,technologyLevel,
			technologyDescription,distanceLY,percentFaster,productionIncreasePercent,costReductionPercent,
			affectsResourceType,prerequisites FROM Technologies t JOIN TechnologyTypes tt ON 
			t.technologyTypeID=tt.technologyTypeID;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			echo '<TABLE id="TechnologyList">';
			echo '<TR><TH>ID</TH><TH>Type</TH><TH>Name</TH><TH>Level</TH><TH>Description</TH><TH>Distance (LY)</TH>
				<TH>% Faster</TH><TH>Production Increase %</TH><TH>Cost Reduction %</TH><TH>Affects Resource Type</TH>
				<TH>Prerequisites</TH>';
			while ($row=$result->fetch_assoc()) {
				echo '<TR>';
				echo "<TD>{$row['technologyID']}</TD>";
				echo "<TD>{$row['technologyTypeDescription']} ({$row['technologyTypeID']})</TD>";
				echo "<TD>{$row['technologyName']}</TD>";
				echo "<TD>{$row['technologyLevel']}</TD>";
				echo "<TD>{$row['technologyDescription']}</TD>";
				echo "<TD>{$row['distanceLY']}</TD>";
				echo "<TD>{$row['percentFaster']}</TD>";
				echo "<TD>{$row['productionIncreasePercent']}</TD>";
				echo "<TD>{$row['costReductionPercent']}</TD>";
				echo "<TD>{$row['affectsResourceType']}</TD>";
				echo "<TD>{$row['prerequisites']}</TD>";
				echo '</TR>';
			}
			echo '</TABLE>';
		}		
	}
}
?>