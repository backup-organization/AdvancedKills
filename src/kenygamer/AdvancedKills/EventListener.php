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
 * @license GNU General Public License v3.0y
 *
 */


namespace kenygamer\AdvancedKills;

use pocketmine\event\Listener;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;
use kenygamer\AdvancedKills\event\PlayerKillEvent;

/**
 * @package kenygamer\AvdancedKills
 * @class EventListener
 */
final class EventListener implements Listener{
	/** @var Main */
	private $plugin;
	
	/**
	 * EventListener constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	
    /**
	 * @param EntityDamageByEntityEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event) : void{
		$entity = $event->getEntity();
		$damager = $event->getDamager();
		if($entity instanceof Player){
			if($damager instanceof Projectile){
				$damager = $damager->getOwningEntity();
			}
			if($damager instanceof Player && (in_array([$entity->getLevel()->getName(), $entity->getLevel()->getFolderName()], $this->plugin->worlds) xor count($this->plugin->worlds) === 0)){
				$damage = $event->getFinalDamage();
			
			    $this->plugin->damagesDealt[$entity->getName()][] = [$damager->getName() => $damage];
			
			    if(!($entity->getHealth() - $damage <= 0) && array_sum($this->plugin->damagesDealt[$entity->getName()]) + $damage >= $entity->getHealth()){
			    	array_shift($this->plugin->damagesDealt[$entity->getName()]);
			    }
			}
		}
	}
	
	/**
	 * @param PlayerDeathEvent $event
	 */
	public function onPlayerDeath(PlayerDeathEvent $event) : void{
		$player = $event->getPlayer();
		if(isset($this->plugin->damagesDealt[$player->getName()])){
			
			$damages = [];
			foreach($this->plugin->damagesDealt[$player->getName()] as $attack){
				foreach($attack as $damager => $damage){
					if(!isset($damages[$damager])){
						$damages[$damager] = 0;
					}
					$damages[$damager] += $damage;
				}
			}
			arsort($damages);
			
			foreach($damages as $damager => $damage){
				$dmger = $this->plugin->getServer()->getPlayerExact($damager);
				if(!($dmger instanceof Player) || !$dmger->isOnline()){
					continue;
				}
				
				$this->plugin->getServer()->getPluginManager()->callEvent(new PlayerKillEvent($dmger, $player));
				
				$playerMoney = EconomyAPI::getInstance()->myMoney($player);
				
				$calculationValue = $this->plugin->calculationValue;
				if(is_string($calculationValue)){
					$range = explode("-", $calculationValue); 
					if(count($range) === 2){
						$calculationValue = mt_rand(min((int) $range[0], (int) $range[1]), max((int) $range[0], $range[1]));
					}
				}
				switch($this->plugin->calculationType){
					case Main::CALCULATION_FIXED: 
					    $money = $calculationValue;
					    break;
					case Main::CALCULATION_PERCENTAGE:
					    $money = $playerMoney * $calculationValue / 100;
					    break;
				}
				foreach($this->plugin->boosts as $boost){
					if($dmger->hasPermission("advancedkills.boost." . $boost)){
						$money *= $boost;
						break;
					}
				}
				if($this->plugin->realisticMode){
					if($money > $playerMoney){
						$money = $playerMoney;
					}
				}
				$money = floor($money);
				if($playerMoney <= $this->plugin->minimumMoney){
					break;
				}
				if($money !== 0 && (!$this->plugin->realisticMode || EconomyAPI::getInstance()->reduceMoney($player, $money) === EconomyAPI::RET_SUCCESS)){
					
					$this->plugin->registerStat($dmger, Main::STAT_KILL);
					$this->plugin->registerStat($player, Main::STAT_DEATH);
					
					EconomyAPI::getInstance()->addMoney($dmger, $money);
					$msg = $this->plugin->translateMessage("kill-message", $player->getName(), number_format($money));
					if($msg != ""){
						$dmger->sendMessage($msg);
					}
					$msg = $this->plugin->translateMessage($this->plugin->deathMessage, $dmger->getName(), number_format($money));
					if($msg != ""){
						$player->sendMessage($msg);
					}
				}else{
					$msg = $this->plugin->translateString("no-money", $player->getName());
					if($msg != ""){
						$dmger->sendMessage($msg);
					}
				}
				break;
			}
		}
	}
	
}