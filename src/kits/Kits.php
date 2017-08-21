<?php namespace kits;

use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\Player;

class Kits extends PluginBase{

	public static $instance;

	public $cddb;

	public $kits = [];

	public function onEnable(){
		self::$instance = $this;
		@mkdir($this->getDataFolder());
		//Cooldown databases are made via kits.
		$this->cddb = new \SQLite3($this->getDataFolder() . "cooldowns.db");
		$this->saveDefaultConfig();
		$this->setupKits();

		$this->getServer()->getCommandMap()->register("kit", new KitCommand($this, "kit", "Choose a kit!"));
	}

	public function onDisable(){
		$this->cddb->close();
	}

	public static function getInstance(){
		return self::$instance;
	}

	public function setupKits(){
		$config = $this->getConfig()->getAll();
		foreach($config["kits"] as $kitname => $stuff){
			$this->kits[$kitname] = new Kit($kitname, $stuff["cooldown"], $this->configToPhysical($stuff["items"]));
		}
	}

	public function getKit($name){
		return $this->kits[$name] ?? false;
	}

	public function availibleKits(Player $sender){
		$string = "Availible kits:\n";
		foreach($this->kits as $kitname => $kit){
			$kits = $this->getKit($kitname);
			if($kits->testPermission($sender)){
			  $string .= $kitname . ",\n ";
			}
		}
		return $string;
	}

	public function configToPhysical(array $items){
		$new_items = [];
		foreach($items as $item){
			$array = explode(":", $item);
			$io = Item::get($array[0],$array[1],$array[2]);
			if(count($array) >= 4){
				$io->setCustomName($array[3]);
			}
			$new_items[] = $io;
		}
		return $new_items;
	}

	public function getCooldownDb(){
		return $this->cddb;
	}

}
