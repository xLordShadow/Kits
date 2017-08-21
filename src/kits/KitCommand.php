<?php namespace kits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class KitCommand extends Command implements PluginIdentifiableCommand{

	public $plugin;

	public function __construct(Kits $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		//$this->setPermission("kits.cmd");
	}

	public function execute(CommandSender $sender, $label, array $args){
		if(count($args) != 1){
			$sender->sendMessage(TextFormat::RED."Usage: /kit <name>");
			$sender->sendMessage($this->plugin->availibleKits($sender));
			return;
		}
		$name = $args[0];
		$kit = $this->plugin->getKit($name);
		if($kit == false){
			$sender->sendMessage(TextFormat::RED."This kit doesn't exist!");
			$sender->sendMessage($this->plugin->availibleKits());
			return;
		}
		if($kit->getName() == "inferno" || $kit->getName() == "turtle" || $kit->getName() == "necromancer" || $kit->getName() == "saber"){
			if($sender->isOp()){
				return;
			}
		}
		if(!$kit->testPermission($sender)){
			$sender->sendMessage(TextFormat::RED."You do not have permission to use this kit!");
			return;
		}
		if(!$kit->isCooledDown($sender)){
			$sender->sendMessage(TextFormat::RED."You can use this kit again in about ".round((($kit->getPlayerCooldown($sender) + (60*60*($kit->getCooldown()))) - time()) / 60 / 60)." hours!");
			return;
		}
		$kit->equip($sender);
		$sender->sendMessage(TextFormat::GREEN."Kit ".$kit->getName()." has been equipped!");
	}

	public function getPlugin(){
		return $this->plugin;
	}

}
