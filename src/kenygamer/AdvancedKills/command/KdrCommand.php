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

namespace kenygamer\AdvancedKills\command;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use jojoe77777\FormAPI\SimpleForm;

use kenygamer\AdvancedKills\Main;

/**
 * @class KdrCommand
 * @namespace kenygamer\AdvancedKills\command
 */
final class KdrCommand extends Command{
	/** @var Main */
	private $plugin;
	
	public function __construct(Main $plugin){
		parent::__construct("kdr", "Shows your kills, deaths, and KDR.", "/kdr [player]", []);
		$this->setPermission("advancedkills.command.kdr");
		$this->plugin = $plugin;
	}
	
	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return true;
		}
		if(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "You must run this command in-game.");
			return true;
		}
		
		$username = array_shift($args);
		if($username !== null){
			$player = $this->plugin->getServer()->getPlayer($username);
			if($player === null){
				$sender->sendMessage(TextFormat::RED . "Player not found.");
				return true;
			}
		}else{
			$player = $sender;
		}
		
		$kills = $this->plugin->getKills($player);
		$deaths = $this->plugin->getDeaths($player);
		$kdr = sprintf("%.2f", $deaths < 1 ? 0 : ($kills / $deaths));
		$form = new SimpleForm(null);
		$form->setTitle(TextFormat::BOLD . $player->getName() . " Stats");
		$form->addButton("Kills: " . TextFormat::BOLD . $kills);
		$form->addButton("Deaths: " . TextFormat::BOLD . $this->plugin->getDeaths($player));
		$form->addButton("KDR: " . TextFormat::BOLD . $kdr);
		$sender->sendForm($form);
		return true;
	}
	
}