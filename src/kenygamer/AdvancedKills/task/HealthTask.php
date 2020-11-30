<?php

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

declare(strict_types=1);

namespace kenygamer\AdvancedKills\task;

use pocketmine\Server;
use pocketmine\scheduler\Task;

use kenygamer\AdvancedKills\Main;

/**
 * @package kenygamer\AdvancedKills\task
 * @class HealthTask
 */
final class HealthTask extends Task{
	/** @var Main */
	private $plugin;
	
	/**
	 * TickHealthTask constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	/**
	 * Called every 1 ticks.
	 *
	 * @return int $currentTick
	 */
	public function onRun(int $currentTick) : void{
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			if($player->getHealth() === $player->getMaxHealth() && isset($this->plugin->damagesDealt[$player->getName()])){
				unset($this->plugin->damagesDealt[$player->getName()]);
			}
		}
	}
	
}