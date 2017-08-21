<?php namespace kits;

use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\Player;

class Kit{

	public $name;
	public $cooldown;

	public $items;

	public $cddb;

	public function __construct(string $name, int $cooldown, array $items){
		$this->name = $name;
		$this->cooldown = $cooldown;

		$this->items = $items;

		$this->cddb = Kits::getInstance()->getCooldownDb();
		$this->cddb->exec("CREATE TABLE IF NOT EXISTS ".$name."(player TEXT PRIMARY KEY COLLATE NOCASE, cooldown INT)");
	}

	public function getName(){
		return $this->name;
	}

	public function getCooldown(){
		return $this->cooldown;
	}

	public function getItems(){
		return $this->items;
	}

	public function equip(Player $player){
		$this->putItems($player->getInventory());
		$this->setCooldown($player);
	}

	public function putItems(Inventory $inventory){
		foreach($this->getItems() as $item){
			$inventory->addItem($item);
		}
	}

	public function setCooldown(Player $player){
		$name = strtolower($player->getName());
		$time = time();
		$this->cddb->exec("INSERT OR REPLACE INTO ".$this->getName()."(player, cooldown) VALUES('$name', '$time')");
	}

	public function hasCooldown(Player $player){
		$name = strtolower($player->getName());
		$query = $this->cddb->query("SELECT player FROM ".$this->getName()." WHERE player='$name'");
		$array = $query->fetchArray(SQLITE3_ASSOC);
		return !empty($array);
	}

	public function isCooledDown(Player $player){
		if(!$this->hasCooldown($player)) return true;
		if(($this->getPlayerCooldown($player) + (60*60*($this->getCooldown()))) - time() <= 0) return true;
		return false;
	}

	public function getPlayerCooldown(Player $player){
		$name = strtolower($player->getName());
		$query = $this->cddb->query("SELECT cooldown FROM ".$this->getName()." WHERE player='$name'");
		$array = $query->fetchArray(SQLITE3_ASSOC);
		return $array["cooldown"];
	}

	public function testPermission(Player $player){
		return $player->hasPermission("advancedkits.".$this->getName());
	}

}
