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

namespace Teaclon\TODOLIST;

// Basic;
use pocketmine\Server;
use pocketmine\utils\Config;

use Teaclon\TSeriesAPI\command\subcommand\BaseCommand;
use Teaclon\TSeriesAPI\command\CommandManager;
use Teaclon\TODOLIST\command\MainCommand;



class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener
{
	const PLUGIN_VERSION       = "1.0.0";
	
	const STRING_PRE           = "TODOLIST";
	const UPDATE_PRE           = self::NORMAL_PRE."§a UPDATE §e>§f ";
	const WARNING_PRE          = "§e".self::STRING_PRE." §r§e>§f ";
	const ERROR_PRE            = "§c".self::STRING_PRE." §r§e>§f ";
	const NORMAL_PRE           = "§b".self::STRING_PRE." §r§e>§f ";
	
	
	
	
	private static $instance   = null;
	private $mypath            = null;
	private $server            = null;
	private $logger            = null;
	private $tsapi             = null;
	private $config            = null;
	
	
	
	public function onLoad()
	{
		if($this->getDescription()->getVersion() > self::PLUGIN_VERSION)
		{
			self::stopThread($this->getName(), "Invalid version.");
		}
		self::$instance = $this;
	}
	
	public function onEnable()
	{
		$start = microtime(true);
		$this->server = $this->getServer();
		$this->logger = $this->getServer()->getLogger();
		if(!$this->server->getPluginManager()->getPlugin("TSeriesAPI"))
		{
			$this->ssm(self::NORMAL_PRE."§c服务器无法找到所依赖的插件!");
			$this->ssm(self::NORMAL_PRE."§c本插件已卸载.");
			$this->server->getPluginManager()->disablePlugin($this);
			return null;
		}
		else $this->tsapi = $this->server->getPluginManager()->getPlugin("TSeriesAPI")->setMeEnable($this);
		
		
		$this->mypath   = $this->getDataFolder(); if(!is_dir($this->mypath)) mkdir($this->mypath, 0777, true);
		$this->todolist = new Config($this->mypath."todolist.yml", Config::YAML);
		
		
		
		
		$this->server->getPluginManager()->registerEvents($this, $this);
		$this->tsapi->getCommandManager()->registerCommand(new MainCommand($this));
		$this->tsapi->getTaskManager()->createCallbackTask($this, "scheduleRepeatingTask", "repeatTODOLIST", [], 20 * 60 * 3);
		
		
		$this->ssm(self::NORMAL_PRE."§d-----------------------------------------------------", "info", "server");
		$this->ssm(self::NORMAL_PRE."§e".self::STRING_PRE." §a加载完毕 §f(内核版本 §dv§a".self::PLUGIN_VERSION."§f)", "info", "server");
		$this->ssm(self::NORMAL_PRE."§e作者: §bTeaclon§f(§e锤子§f)", "info", "server");
		$this->ssm(self::NORMAL_PRE."§f插件主指令: §d/§6".MainCommand::MY_COMMAND."", "info", "server");
		$this->ssm(self::NORMAL_PRE."§e耗时: §6".round(microtime(true) - $start, 3)."§as", "info", "server");
		$this->ssm(self::NORMAL_PRE."§d-----------------------------------------------------", "info", "server");
		
	}
	
	public function onDisable()
	{
		$this->ssm(self::NORMAL_PRE."§c插件已卸载.", "info", "server");
	}
	
	
	
	
	public function repeatTODOLIST()
	{
		foreach($this->todolist()->getAll() as $id => $data)
		{
			$this->ssm(self::NORMAL_PRE."   - §d{$id} §f-    §cDO IT NOW!!!!!!!!!!!!!!!!!!!!!!!!", "info", "server");
			$this->ssm(self::NORMAL_PRE."§ePROGRAMM:   §f".$data["programm"], "info", "server");
			$this->ssm(self::NORMAL_PRE."§eTODO-MSG:   §f".$data["msg"], "info", "server");
			$this->ssm(self::NORMAL_PRE."§eSTATUS:     §f".($data["status"] ? "§bdeveloping" : "§adone §6xD"), "info", "server");
			$this->ssm(self::NORMAL_PRE."§eSTART-TIME: §f".$data["start_time"], "info", "server");
			$this->ssm(self::NORMAL_PRE."§eDONE-TIME:  §f".($data["done_time"] ? $data["done_time"] : "§bnot done"), "info", "server");
		}
	}
	
	
	
#---[BASIC FUNCTIONS]--------------------------------------------------------------------------------------------#
	/**
		用法: self::ssm(信息, 日志记录等级, 发送形式)
	**/
	public final function ssm($msg, $level = "info", $type = "logger")
	{
		if(($msg === "") || ($level === "") || ($type === ""))
		{
			Server::getInstance()->getLogger()->error(self::NORMAL_PRE."[LOGGER] Error Usage(0010)");
		}
		elseif(!\in_array($level, ["info", "warning", "error", "notice", "debug", "alert", "critical", "emergency"]))
		{
			Server::getInstance()->getLogger()->error(self::NORMAL_PRE."[LOGGER] Error Usage(0015)");
		}
		elseif(!\in_array($type, ["server", "logger"]))
		{
			Server::getInstance()->getLogger()->error(self::NORMAL_PRE."[LOGGER] Error Usage(0020)");
		}
		else
		{
			$color = ($level === "notice") ? "§r§b" : null;
			if($type === "server") Server::getInstance()->getLogger()->$level($color.$msg);
			elseif($type === "logger") $this->getLogger()->$level($color.$msg);
		}
	}
	
	public static final function stopThread($plugin_name, $msg, $error_code = "")
	{
		Server::getInstance()->getLogger()->error("§c§l服务器已崩溃, 正在关闭服务器.");
		Server::getInstance()->getLogger()->error("§c§l服务器已崩溃, 正在关闭服务器.");
		Server::getInstance()->forceshutdown();
		if($error_code === "") $error_code = "NULL";
		exit("ERROR: >> Plugin: {$plugin_name}; Cause: {$msg}; Code: {$error_code}".PHP_EOL);
	}
	
	public static final function getInstance()
	{
		return self::$instance;
	}
	
	public final function getTSApi() : \Teaclon\TSeriesAPI\Main
	{
		return $this->tsapi;
	}
	
	
#---[CONFIG FUNCTIONS]--------------------------------------------------------------------------------------------#
	public final function todolist() : Config
	{
		return $this->todolist;
	}
	
}
?>