<?php

/*                             Copyright (c) 2017-2018 TeaTech All right Reserved.
 *
 *      ████████████  ██████████           ██         ████████  ██           ██████████    ██          ██
 *           ██       ██                 ██  ██       ██        ██          ██        ██   ████        ██
 *           ██       ██                ██    ██      ██        ██          ██        ██   ██  ██      ██
 *           ██       ██████████       ██      ██     ██        ██          ██        ██   ██    ██    ██
 *           ██       ██              ████████████    ██        ██          ██        ██   ██      ██  ██
 *           ██       ██             ██          ██   ██        ██          ██        ██   ██        ████
 *           ██       ██████████    ██            ██  ████████  ██████████   ██████████    ██          ██
**/

namespace Teaclon\TODOLIST\command;

use Teaclon\TODOLIST\Main;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use Teaclon\TSeriesAPI\command\subcommand\BaseCommand;
use Teaclon\TSeriesAPI\command\CommandManager;


class MainCommand extends BaseCommand
{
	const MY_COMMAND             = "todolist";
	const MY_COMMAND_PEREMISSION = [self::PERMISSION_NONE];
	public $myprefix = Main::NORMAL_PRE;
	private $tsapi = null;
	
	
	
	
	
	public function __construct(Main $plugin)
	{
		$this->tsapi = $plugin->getTSApi();
		// CommandName, Description, usage, aliases, overloads;
		$this->init($plugin, self::MY_COMMAND, Main::STRING_PRE."的主指令", null, [], []);
	}
	
	
	
	public function execute(CommandSender $sender, $commandLabel, array $args)
	{
		$senderName = strtolower($sender->getName());
		if(!isset($args[0]))
		{
			$this->sendMessage($sender, "§e--------------§b".Main::STRING_PRE."指令助手§e--------------");
			foreach(self::getHelpMessage() as $cmd => $message)
			{
				if($this->hasSenderPermission($sender, $cmd))
					$this->sendMessage($sender, str_replace("{cmd}", self::MY_COMMAND, $message));
				else continue;
			}
			$this->sendMessage($sender, "§e---------------------------");
			return true;
		}
		
		switch($args[0])
		{
			default:
			case "help":
			case "帮助":
				$this->execute($sender, $commandLabel, []);
				return true;
			break;
			
			
			case "add":
				if(!$this->hasSenderPermission($sender, $args[0]))
				{
					$this->sendMessage($sender, "§c你没有权限使用这个指令.");
					return true;
				}
				if($sender instanceof Player)
				{
					$sender->sendMessage($this->plugin->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.notFound"));
					return true;
				}
				if(!isset($args[1], $args[2]))
				{
					$this->sendMessage($sender, "§c缺少参数.");
					return true;
				}
				$this->plugin->todolist()->set($args[1], 
				[
					"programm"   => $args[1],
					"msg"        => $args[2],
					"status"     => true,
					"start_time" => date("Y-m-d H:i:s"),
					"done_time"  => \null,
				]);
				$this->plugin->todolist()->save();
				$this->sendMessage($sender, "§aADDED! PLEASE DO IT NOW §cxD§a!");
				return true;
			break;
			
			
			case "done":
				if(!$this->hasSenderPermission($sender, $args[0]))
				{
					$this->sendMessage($sender, "§c你没有权限使用这个指令.");
					return true;
				}
				if($sender instanceof Player)
				{
					$sender->sendMessage($this->plugin->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.notFound"));
					return true;
				}
				if(!isset($args[1]))
				{
					$this->sendMessage($sender, "§cMiss parameter.");
					return true;
				}
				$a = $this->plugin->todolist()->getAll();
				$a[$args[1]]["status"]    = \false;
				$a[$args[1]]["done_time"] = date("Y-m-d H:i:s");
				$this->plugin->todolist()->setAll($a);
				$this->plugin->todolist()->save();
				$this->sendMessage($sender, "§a已§cDone§a这个§bTODOLIST §exD");
				return true;
			break;
			
			
			case "look":
				if(!$this->hasSenderPermission($sender, $args[0]))
				{
					$this->sendMessage($sender, "§c你没有权限使用这个指令.");
					return true;
				}
				if($sender instanceof Player)
				{
					$sender->sendMessage($this->plugin->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.notFound"));
					return true;
				}
				foreach($this->plugin->todolist()->getAll() as $id => $data)
				{
					$this->sendMessage($sender, "   - §d{$id} §f- ");
					$this->sendMessage($sender, "§ePROGRAMM:   §f".$data["programm"]);
					$this->sendMessage($sender, "§eTODO-MSG:   §f".$data["msg"]);
					$this->sendMessage($sender, "§eSTATUS:     §f".($data["status"] ? "§bdeveloping" : "§adone §6xD"));
					$this->sendMessage($sender, "§eSTART-TIME: §f".$data["start_time"]);
					$this->sendMessage($sender, "§eDONE-TIME:  §f".($data["done_time"] ? $data["done_time"] : "§bnot done"));
				}
				return true;
			break;
		}
	}
	
	public static function getCommandPermission(string $cmd)
	{
		$cmds = 
		[
			"add"  => [self::PERMISSION_CONSOLE],
			"done" => [self::PERMISSION_CONSOLE],
			"look" => [self::PERMISSION_CONSOLE],
		];
		
		$cmd = strtolower($cmd);
		return isset($cmds[$cmd]) ? $cmds[$cmd] : "admin";
	}
	
	public static function getHelpMessage() : array
	{
		return 
		[
			"add"    => "用法: §d/§6{cmd} add §f<§eid§f> §f<§eText§f>  添加一个开发备注",
			"done"   => "用法: §d/§6{cmd} done §f<§eid§f>        结束一个开发备注 ",
			"look"   => "用法: §d/§6{cmd} look             §f查看所有开发备注 ",
		];
	}
	
}
?>