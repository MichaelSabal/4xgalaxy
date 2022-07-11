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
		$this->view = 0;
	} // __construct()
	public function render(int $v) {
		if ($v >= 0) $this->view = $v;
		$fn = "render$v";
		if (function_exists($this->$fn)) echo $this->$fn;
	} // render()
	private function render0() {
		$html = '';
		// Welcome, player
		$name = $this->player->getAlias();
		if (empty($name)) $name = $this->player->getFirstName() . ' ' . $this->player->getLastName();
		$html .= "<LABEL>Welcome, $name.</LABEL><BR />";
		// List first star, list planets, population, health
		$starmgr->listStars(-1,$this->player->getPlayerID());
		// List resources, production, consumption
		// List technologies undergoing research
		// List tasks
		return $html;
	} // render0 - first page after login
} // class PlayerDashboard