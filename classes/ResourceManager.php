<?php
class ResourceManager {
	private $dbconn = null;
	public function __construct($conn) {
		if (get_class($conn)=='mysqli') $this->dbconn = $conn;
	}
	public function resourceTypeSelect() {
		$q = "SELECT resourceTypeID,resourceTypeDescription,primaryUsage,secondUsage,thirdUsage,fourthUsage FROM ResourceTypes;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			
		}
		
	}
	public function resourceUsageSelect() {
		$q = "SELECT resourceTypeUsageID,resourceTypeUsageDescription FROM ResourceTypeUsages;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			
		}
		
	}
	public function listAllResources() {
		$q = "SELECT resourceID,approved,renewable,resourceName,resourceDescription,resourceTypeID,resourcePrimaryUsage,resourceSource,playerID,
			resourceCreation,population,basicTimeToProduce,technologyRequired,basicRenewalRate,basicDecayRate FROM Resources;";
		$result = $this->dbconn->query($q);
		if ($result!==false) {
			echo '<TABLE id="resourceList">';
			echo '<TR><TH>ID</TH><TH>Approved</TH><TH>Renewable</TH><TH>Name</TH><TH>Description</TH><TH>Type ID</TH><TH>Primary Usage</TH>
				<TH>Source</TH><TH>Player ID</TH><TH>Created by</TH><TH>Population</TH><TH>Basic Time to Produce</TH><TH>Technology Required</TH>
				<TH>Basic Renewal Rate</TH><TH>Basic Decay Rate</TH></TR>';
			while ($row=$result->fetch_assoc()) {
				echo '<TR>';
				echo "<TD>{$row['resourceID']}</TD>";
				echo "<TD>{$row['approved']}</TD>";
				echo "<TD>{$row['renewable']}</TD>";
				echo "<TD>{$row['resourceName']}</TD>";
				echo "<TD>{$row['resourceDescription']}</TD>";
				echo "<TD>{$row['resourceTypeID']}</TD>";
				echo "<TD>{$row['resourcePrimaryUsage']}</TD>";
				echo "<TD>{$row['resourceSource']}</TD>";
				echo "<TD>{$row['playerID']}</TD>";
				echo "<TD>{$row['resourceCreation']}</TD>";
				echo "<TD>{$row['population']}</TD>";
				echo "<TD>{$row['basicTimeToProduce']}</TD>";
				echo "<TD>{$row['technologyRequired']}</TD>";
				echo "<TD>{$row['basicRenewalRate']}</TD>";
				echo "<TD>{$row['basicDecayRate']}</TD>";
				echo '</TR>';
			}
			echo '</TABLE>';
		}
		
	}
	public function resourceConsumptionList() {
		
	}
}
?>