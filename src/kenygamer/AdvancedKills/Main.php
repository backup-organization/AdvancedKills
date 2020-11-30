<?php

declare(strict_types=1);

/**
 *              _                               _ _  ___ _ _     
 *     /\      | |                             | | |/ (_) | |    
 *    /  \   __| |_   ____ _ _ __   ___ ___  __| | ' / _| | |___ 
 *   / /\ \ / _` \ \ / / _` | '_ \ / __/ _ \/ _` |  < | | | / __|
 *  / ____ \ (_| |\ V / (_| | | | | (_|  __/ (_| | . \| | | \__ \
 * /_/    \_\__,_| \_/ \__,_|_| |_|\___\___|\__,_|_|\_\_|_|_|___/                                                           
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author kenygamer
 * @link github.com/kenygamer
 * @copyright
 * @license GNU General Public License v3.0
 *
 */

namespace kenygamer\AdvancedKills;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\scheduler\Task;
use kenygamer\AdvancedKills\command\KdrCommand;
use kenygamer\AdvancedKills\task\HealthTask;
use specter\api\DummyPlayer;

/**
 * @package kenygamer\AdvancedKills
 * @class Main
 */
final class Main extends PluginBase{
	public const CALCULATION_FIXED = 0;
	public const CALCULATION_PERCENTAGE = 1;
	
	public const STAT_KILL = 1;
	public const STAT_DEATH = 2;
	
	/** @var Config */
	private $stats;
	/** @var Config */
	private $lang;
	/** @var array */
	private $configDefs;
	/** @var array<string, array> */
	public $damagesDealt = [];
	/** @var int[] */
	public $boosts = [];
	/** @var int One of self::CALCULATION_* constants */
	public $calculationType;
	/** @var float|string */
	public $calculationValue;
	/** @var float */
	public $minimumMoney;
	/** @var bool */
	public $realisticMode;
	/** @var string. */
	public $killMessage, $deathMessage, $noMoneyMessage;
	/** @var string[] */
	public $worlds;
	
	/**
	 * Called when the plugin enables.
	 */
	public function onEnable() : void{
		foreach(["config.yml", "lang.properties"] as $fname){
			$this->saveResource($fname, false);
		}
		$this->lang = new Config($this->getDataFolder() . "lang.properties", Config::PROPERTIES);
		
		if(!$this->loadConfig()){
			$this->getLogger()->critical("Plugin configuration is not correctly set up. Check the main repository for reference or let the plugin regenerate the default configuration deleting the existing one.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}else{
		
			$plugin = $this;
			$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick) use($plugin) : void{
				if($plugin->isEnabled()){
					if($plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI") !== null){
						new EventListener($plugin);
						$specter = (new DummyPlayer("Dummy"))->getPlayer();
						$specter->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
					}else{
						$plugin->getLogger()->warning("EconomyAPI plugin not found. Economy support is disabled.");
					}
				}
			}), 1);
					
			$this->getScheduler()->scheduleRepeatingTask(new HealthTask($this), 1);
			
			$this->getServer()->getCommandMap()->register("advancedkills", new KdrCommand($this));
			
			$this->stats = new Config($this->getDataFolder() . "stats.yml", Config::YAML);
		}
	}
	
	/**
	 * Called ahen the plugin disables.
	 */
	public function onDisable() : void{
		if($this->stats !== null){
			$this->stats->save();
		}
	}
	
	/**
	 * Registrr a kill or death.
	 * @param Player|string $player
	 * @param int $stat
	 */
	public function registerStat($player, int $stat) : void{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		switch($stat){
			case self::STAT_KILL:
				$this->stats->setNested($player . ".kills", $this->getKills($player) + 1);
				break;
			case self::STAT_DEATH:
				$this->stats->setNested($player . ".deaths", $this->getDeaths($player) + 1);
				break;
			default:
				throw new \InvalidArgumentException("Argument 2 must be a valid stat");
		}
	}
	
	/**
	 * Returns the player kills.
	 * @param Player|string $player
	 * @return int
	 */
	public function getKills($player) : int{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		return $this->stats->getNested($player . ".kills", 0);
	}
	
	/**
	 * Returns the player deaths.
	 * @param Player|string $player
	 * @return int
	 */
	public function getDeaths($player) : int{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		return $this->stats->getNested($player . ".deaths", 0);
	}
	
	
	/**
	 * Sets the config defaults.
	 */
	public function setConfigDefaults() : void{
		$fp = $this->getResource("config.yml");
		if($fp !== null){
			$this->configDefs = yaml_parse(stream_get_contents($fp));
		}
		@fclose($fp);
	}
	
	/**
	 * Get a config key. Fallback to the default if not present or not of expected type.
	 *
	 * @param string $key
	 * @param string $expectedType
	 *
	 * @return mixed
	 */
	public function getConfigKey(string $key, string $expectedType = ""){
	    $value = $this->getConfig()->getNested($key);
		
	    $expected = $expectedType === "";
	    switch($expectedType){
	    	case "str":
	    	case "string":
	    	    $expected = is_string($value);
	    	    break;
	    	case "bool":
	    	case "boolean":
	    	    $expected = is_bool($value);
	    	    break;
	    	case "int":
	    	case "integer":
	    	    $expected = is_int($value);
	    	    break;
	    	case "float":
	    	    $expected = is_float($value) || is_int($value);
	    	    break;
	    	case "arr":
	    	case "array":
	    	    $expected = is_array($value);
	    	    break;
	    }
	    if(!$expected){
	    	$this->getLogger()->warning("Config key `" . $key . "` not of expected type " . $expectedType);
	    	$steps = explode(".", $key);
	    	$value = $this->configDefs;
	    	foreach($steps as $step){
	    		$value = $value[$step];
	    	}
	    	return $value;
	    }
	    return $value;
	}
	
	/**
	 * Load plugin configuration.
	 *
	 * @return bool true if the config is valid or false if the config is corrupted.
	 */
	private function loadConfig() : bool{
		$boosts = $this->getConfigKey("boosts", "array");
		if($boosts === null){
			return false;
		}
		foreach($boosts as $boost){
			if(in_array($boost, $this->boosts) || !is_int($boost)){
				return false;
			}
			$this->boosts[] = $boost;
			PermissionManager::getInstance()->addPermission(new Permission("advancedkills.boost." . $boost, "Multiplies your kill money reward by factor " . $boost, Permission::DEFAULT_FALSE));
		}
		sort($this->boosts);
		$this->boosts = array_reverse($this->boosts);
		
		$this->calculationType = $this->getConfigKey("calculation.type", "int");
		if($this->calculationType !== self::CALCULATION_FIXED && $this->calculationType !== self::CALCULATION_PERCENTAGE){
			return false;
		}
		$this->calculationValue = $this->getConfig()->getNested("calculation.value");
		$this->minimumMoney = $this->getConfigKey("minimum-money", "float");
		$this->realisticMode = $this->getConfigKey("realistic-mode", "bool");
		$this->worlds = $this->getConfigKey("worlds", "array");
		return true;
	}
	
	/**
	 * Translate a string with the given parameters.
	 *
	 * @param string $key
	 * @param mixed ...$params 
	 *
	 * @return string
	 */
	public function translateString(string $key, ...$params) : string{
		$msg = $this->lang->getNested($key, null);
		if($msg === null){
			$this->getLogger()->error("Language key " . $key . " not found");
			return $key;
		}
		foreach($params as $i => $param){
			$msg = str_replace("{%" . $i . "}", $param, $msg);
		}
		$colors = (new \ReflectionClass(TextFormat::class))->getConstants();
		foreach($colors as $color => $code){
			$msg = str_ireplace("{" . $color . "}", $code, $msg);
		}
		return TextFormat::colorize($msg);
	}
	
}