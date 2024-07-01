<?php

namespace Noob;

/*
███╗   ██╗██╗  ██╗██╗   ██╗████████╗    ██████╗ ███████╗██╗   ██╗
████╗  ██║██║  ██║██║   ██║╚══██╔══╝    ██╔══██╗██╔════╝██║   ██║
██╔██╗ ██║███████║██║   ██║   ██║       ██║  ██║█████╗  ██║   ██║
██║╚██╗██║██╔══██║██║   ██║   ██║       ██║  ██║██╔══╝  ╚██╗ ██╔╝
██║ ╚████║██║  ██║╚██████╔╝   ██║       ██████╔╝███████╗ ╚████╔╝ 
╚═╝  ╚═══╝╚═╝  ╚═╝ ╚═════╝    ╚═╝       ╚═════╝ ╚══════╝  ╚═══╝  
        Copyright © 2024 - 2025 NoobMCGaming
*/                                               


use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Server;
use pocketmine\event\Listener as L;
use Noob\commands\{PlayerShopCommand};
use muqsit\invmenu\InvMenuHandler;
use Noob\Libs;


class PlayerShop extends PluginBase implements L{

    public $players;
    public $data;
	public static $instance;

    public $ERROR_MSG = [
        "addItem" => "§f/pshop additem < price >",
        "removeitem" => "§f/pshop removeitem",
        "open-playershop" => "§f/pshop open <tên player>",
        "myshop" => "§f/pshop myshop",
        "cannot-additem" => "§fBạn Đã Không Thể Bán Thêm Vật Phẩm !",
        "item-null" => "§fVui Lòng Cầm Vật Phẩm Để Bán !",
        "No-Have-Shop" => "§fShop Không Tồn Tại !"
    ];

	public static function getInstance() : self {
		return self::$instance;
	}

	public function onEnable(): void{
        self::$instance = $this;
        $this->getServer()->getCommandMap()->register("/pshop", new PlayerShopCommand($this));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->data = new Config($this->getDataFolder() ."config.yml", Config::YAML);
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
	}

    public function getDataManager(){
        return $this->data;
    }

    public function getData(Player $player){
        return $this->getDataManager()->get($player->getName());
    }

    public function getDataByName(string $playerName){
        return $this->getDataManager()->get($playerName);
    }

    public function getPathData(){
        return $this->getDataFolder();
    }

    public function getMessage(string $type): string{
        if (!array_key_exists($type, $this->ERROR_MSG)) {
            return "Key not exists !";
        }
        return $this->ERROR_MSG[$type];
    }

    public function onJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();
        if(!$this->getDataManager()->exists($player->getName())){
            $this->getDataManager()->set($player->getName(), []);
            $this->getDataManager()->save();
        }
    }

    public function canSellItem(Player $player): bool{
        if($this->getDataManager()->get($player->getName()) === []) return true;
        $count = 0;
        foreach($this->getDataManager()->get($player->getName()) as $data){
            $count++;
        }
        if($count < 55) return true;
        return false;
    }

    public function addItem(Player $player, float $price): void{
        $item = $player->getInventory()->getItemInHand();
        if(!$item->isNull()){
            $encode = Libs::getInstance()->itemToData($item);
            $format = (string)$price . ":" . $encode;
            $insert = [$format];
            if($this->getDataManager()->get($player->getName()) === []){
                $this->getDataManager()->set($player->getName(), array_merge($this->getDataManager()->get($player->getName()), $insert));
                $this->getDataManager()->save();
            }
            else{
                $this->getDataManager()->set($player->getName(), array_merge($this->getDataManager()->get($player->getName()), $insert));
                $this->getDataManager()->save();
            }
        }
    }

    public function removeItem(Player $player, string $encode_item){
        $shop = $this->getDataManager()->get($player->getName());
        $this->getDataManager()->set($player->getName(), []);
        $this->getDataManager()->save();
        foreach($shop as $data => $value){
            $ex = explode(":", $value);
            if($ex[1] != $encode_item){
                $insert = [$value];
                if($this->getDataManager()->get($player->getName()) === []){
                    $this->getDataManager()->set($player->getName(), array_merge($this->getDataManager()->get($player->getName()), $insert));
                    $this->getDataManager()->save();
                }
                else{
                    $this->getDataManager()->set($player->getName(), array_merge($this->getDataManager()->get($player->getName()), $insert));
                    $this->getDataManager()->save();
                }
            }
        }
    }

    public function removeItemByName(string $playerName, string $encode_item){
        $shop = $this->getDataByName($playerName);
        $this->getDataManager()->set($playerName, []);
        $this->getDataManager()->save();
        foreach($shop as $data => $value){
            $ex = explode(":", $value);
            if($ex[1] != $encode_item){
                $insert = [$value];
                if($this->getDataManager()->get($playerName) === []){
                    $this->getDataManager()->set($playerName, array_merge($this->getDataManager()->get($playerName), $insert));
                    $this->getDataManager()->save();
                }
                else{
                    $this->getDataManager()->set($playerName, array_merge($this->getDataManager()->get($playerName), $insert));
                    $this->getDataManager()->save();
                }
            }
        }
    }

    public function hasItem(string $playerName, string $encode_item): bool{
        $shop = $this->getDataByName($playerName);
        foreach($shop as $data => $value){
            $ex = explode(":", $value);
            if($ex[1] == $encode_item){
                return true;
            }
        }
        return false;
    }
}