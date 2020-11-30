# AdvancedKills

![](https://u.cubeupload.com/kenygamer/5B07A8FCE14046279269.png)

![](https://img.shields.io/codeclimate/issues/kenygamer/AdvancedKills) ![](https://img.shields.io/codeclimate/coverage/kenygamer/AdvancedKills) ![](https://img.shields.io/codeclimate/maintanability/kenygamer/AdvancedKills) ![](https://img.shields.io/github/repo-size/kenygamer/AdvancedKills) ![](https://img.shields.io/github/downloads/kenygamer/AdvancedKills/total) ![](https://img.shields.io/github/issues/kenygamer/AdvancedKills) ![](https://img.shields.io/github/license/kenygamer/AdvancedKills) ![](https://img.shields.io/github/followers/kenygamer) ![](https://img.shields.io/github/v/release/kenygamer/AdvancedKills) ![](https://img.shields.io/github/last-commit/AdvancedKills)

The next-generation plugin for better kill management on your server.

## What is this plugin?
This plugin provides a better kill management for your server, from commands, fair kill reward money plugin￼￼ and an API for developers.

## What is a fair kill?

In the plugin, you'll hear a lot of the term "fair kill". In essence, it is what makes this plugin special.

Fair kill is a kill that is logged, unlike the normal kill, by the amount of damage it inflicted on another in a PvP round. For example, many people have ever died because of an arrow, but the arrow was not the reason that you had critical health. It could have been the zombies, or whatever.

## Features
- /kdr command to to display your or another player's kills, deaths, and kill death ratio.
- API for developers to get the "fair kills" of a player.
- Adds a "fair kill" reward system:
    * Give money to players using EconomyAPI plugin.
    * Make boosts/multipliers assignable to players via permissions (consider using a permission manager, such as: [PurePerms](https://github.com/poggit-orphanage/PurePerms) or [Hierarchy](https://github.com/CortexPE/Hierarchy).)
    * Realistic mode, which prevents players from farming kill.
    * An option to configure if the killer should receive a percentage of money or a fixed money.
    * An option to enable which worlds the plugin should work on, useful for networks.
    * All configurable messages.
- All configurable messages.

## Commands

| Command | Usage | Description | Permission |
| ------- | ----- | ----------- | ---------- |
| `/kdr` | `/kdr [player]` | Shows the player kills, deaths and KDR. | `advancedkills.command.kdr`

## Developers
The developer API is extremely easy. The Main class has everything we need.

### `kenygamer\AdvancedKills\Main`

- `getKill(Player|string $player)`: Get the number of kills of the player.
- `getDeaths(Player|string player)`: Get the number of kills of the player.

### `kenygamer\AdvancedKills\PlayerKillEvent`

This event is called when a fair kill happens. You can listen to it by adding the import:
```php
use kenygamer\AdvancedKills\PlayerKillEvent;
```
Registering the listener:
```php
/** @var \pocketmine\plugin\PluginBase $this */
$this->getServer()->getPluginManager()->registerEvents($this, $this);
```

And adding the listener:

```php
function onPlayerKill(PlayerKillEvent $event){
    
}
```



If you are creating a plugin that uses AdvancedKills, be sure to add it as a dependency in your `plugin.yml` appending the line:
```yaml
depend: [AdvancedKills]
```
# Database support
Database support is debated for now as I believe that adding database support is negligible for two stats, however, you can always listen to the `PlayerKillEvent` and log it in your database if you don't want to use the default.

## Issues
Before reporting an issue please bare in mind:
- We do not support spoons. Any issue issue wirh PocketMine-MP forks will be ignored.
- Do not blank out the issue template or your issue will be instantly closed.
- Please make sure you use the latest PocketMine-MP version.

If you want to contact me outside of Gitub you can, [join my support server](https://kenygamer.us.to/discord)

## How can I follow along/contribute?
Contributions and donations are greatly appreciated.

- Star this repository. It's free.
- Submit pull requests to contribute code.
- ~~Donate to my PayPal.~~

## License
This plugin is licensed under GNU General Public License v3.0. 