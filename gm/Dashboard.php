<?php
spl_autoload_register(function ($class) {
    include '../classes/' . $class . '.php';
});
class Dashboard {
	private $dbconn = null;
	private $blockclass = '';
	private $galaxy = null;
	private $star = null;
	private $players = null;
	public function __construct($conn,$bcname='dashboard') {
		if (get_class($conn)=='mysqli') {
			$this->dbconn = $conn;
			$this->blockclass = $bcname;
			$this->galaxy = new Galaxy($this->dbconn);
			$this->star = new StarManager($this->dbconn);
			$this->players = new PlayerManager($this->dbconn);
		}
	}
	public function showServerBlock($blockid='') {
		$apachever = $_SERVER['SERVER_SOFTWARE'];
		$apachever = substr($apachever,0,strpos($apachever,' '));
		$block = '<FIELDSET id="'.$blockid.'" class="'.$this->blockclass.'">';
		$block .= '<LABEL>Server name: </LABEL>'.$_SERVER['SERVER_NAME'].'<BR />';
		$block .= '<LABEL>Server version: </LABEL>'.$apachever.'<BR />';
		$block .= '<LABEL>PHP version: </LABEL>'.phpversion().'<BR />';
		$block .= '<LABEL>MySQL version: </LABEL>'.$this->dbconn->get_server_info().'<BR />';
		$block .= '</FIELDSET>';
		echo $block;
	}
	public function showGalaxyBlock($blockid='') {
		$galaxysize = $this->galaxy->getSize();
		$block = '<FIELDSET id="'.$blockid.'" class="'.$this->blockclass.'">';
		$block .= '<LABEL>Galaxy version: </LABEL>'.$this->galaxy->getVersion().'<BR />';
		$block .= '<LABEL>Game calendar: </LABEL>'.$this->galaxy->getGameCalendar().'<BR />';
		$block .= '<LABEL>Galaxy size: </LABEL>'.$galaxysize[0].' x '.$galaxysize[1].'<BR />';
		$block .= '<LABEL>Max stars: </LABEL>'.$this->galaxy->getMaxStars().'<BR />';
		$block .= '<BUTTON id="GrowGalaxy" onClick="onClickGrowGalaxyButton();">Grow the galaxy</BUTTON><BR />';
		$block .= '</FIELDSET>';
		echo $block;
	}
	public function showStarBlock($blockid='') {
		$block = '<FIELDSET id="'.$blockid.'" class="'.$this->blockclass.'">';
		$block .= '<LABEL>Total stars: </LABEL>'.$this->star->getCount().'<BR />';
		$block .= '<LABEL>Habitable stars: </LABEL>'.$this->star->getHabitableCount().'<BR />';
		$block .= '<LABEL>Human stars: </LABEL>'.$this->star->getHumanCount().'<BR />';
		$block .= '<LABEL>Computer stars: </LABEL>'.$this->star->getAICount().'<BR />';
		$block .= '<BUTTON id="AddStar" onClick="onClickAddStarButton();">Add a star</BUTTON><BR />';
		$block .= '</FIELDSET>';
		echo $block;		
	}
	public function showStarColorBlock($blockid='') {
		$block = '<FIELDSET id="'.$blockid.'" class="'.$this->blockclass.'">';
		$block .= $this->star->echoColorListAsLabel();
		$block .= '</FIELDSET>';
		echo $block;		
	}
	public function showPlayerBlock($blockid='') {
		$block = '<FIELDSET id="'.$blockid.'" class="'.$this->blockclass.'">';
		$block .= '<LABEL>Total players: </LABEL>'.$this->players->countPlayers().'<BR />';
		$block .= '<LABEL>Human: </LABEL>'.$this->players->countHumanPlayers().'<BR />';
		$block .= '<LABEL>Computer: </LABEL>'.$this->players->countComputerPlayers().'<BR />';
		$block .= '<LABEL>Almost expired: </LABEL>'.$this->players->countAlmostExpiredPlayers().'<BR />';
		$block .= '<LABEL>Fossilized: </LABEL>'.$this->players->countExpiredPlayers().'<BR />';
		$block .= '<BUTTON id="AddComputerPlayer" onClick="onClickAddComputerPlayerButton();">Add a computer player</BUTTON><BR />';
		$block .= '</FIELDSET>';
		echo $block;		
	}
}
?>