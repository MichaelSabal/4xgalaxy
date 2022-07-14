<?php
class PlayerDashboard {
	private $dbconn;
	private $player;
	private $starmgr;
	private $resmgr;
	private $techmgr;
	private $planetmgr;
	private $view;
	public function __construct(MySQLi $conn, Player $p) {
		$this->dbconn = $conn;
		$this->player = $p;
		$this->starmgr = new StarManager($conn);
		$this->planetmgr = new PlanetManager($conn);
		$this->resmgr = new ResourceManager($conn);
		$this->techmgr = new TechnologyManager($conn);
		$this->view = $_SESSION['dashboardView'] ?? 0;
	} // __construct()
	public function render(int $v = -1) {
		if ($v == -1) $v = $this->view;
		if ($v >= 0) $_SESSION['dashboardView'] = $this->view = $v;
		$fn = "render$v";
		if (is_callable([$this,$fn])) echo $this->$fn();
	} // render()
	private function render0() {
		$html = '';
		// Welcome, player
		$name = $this->player->getAlias();
		if (empty($name)) $name = $this->player->getFirstName() . ' ' . $this->player->getLastName();
		$html .= "<DIV class=\"header\">";
		$html .= "<LABEL>Welcome, $name.</LABEL><BR />";
		$html .= "</DIV>";
		$html .= "<DIV class=\"maincontent\">";
		// List first star, list planets, population, health
		$html .= $this->starmgr->listStars(-1,$this->player->getID());
		$starIDs = $this->starmgr->getStarIDs();
		if (count($starIDs)>0) {
			$html .= $this->planetmgr->listObjects($starIDs[0],$this->player->getID());
		}
		// List resources, production, consumption
		// List technologies undergoing research
		// List tasks
		$html .= "</DIV>";
		return $html;
	} // render0 - first page after login
} // class PlayerDashboard
