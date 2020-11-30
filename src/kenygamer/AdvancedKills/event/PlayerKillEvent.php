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

declare(strict_types=1);

namespace kenygamer\AdvancedKills\event;

use pocketmine\Player;
use pocketmine\event\Event;

/**
 * @package kenygamer\AdvancedKills\task
 * @class PlayerKillEvent
 */
class PlayerKillEvent extends Event{
	/** @var Player */
	private $killer;
	/** @var Player */
	private $victim;
	
	/**
	 * @param Player $killer
	 * @param Player $victim
	 */
	public function __construct(Player $killer, Player $victim){
		$this->killer = $killer;
		$this->victim = $victim;
	}
	
	/**
	 * Returns the player that was killed.
	 *
	 * @return Player
	 */
	public function getVictim() : Player{
		return $this->victim;
	}
	
	/**
	 * Returns the player that killed the other.
	 *
	 * @return Player
	 */
	public function getKiller() : Player{
		return $this->killer;
	}
	
}