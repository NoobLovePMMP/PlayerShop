<?php

namespace Noob\commands;

/*
███╗   ██╗██╗  ██╗██╗   ██╗████████╗    ██████╗ ███████╗██╗   ██╗
████╗  ██║██║  ██║██║   ██║╚══██╔══╝    ██╔══██╗██╔════╝██║   ██║
██╔██╗ ██║███████║██║   ██║   ██║       ██║  ██║█████╗  ██║   ██║
██║╚██╗██║██╔══██║██║   ██║   ██║       ██║  ██║██╔══╝  ╚██╗ ██╔╝
██║ ╚████║██║  ██║╚██████╔╝   ██║       ██████╔╝███████╗ ╚████╔╝ 
╚═╝  ╚═══╝╚═╝  ╚═╝ ╚═════╝    ╚═╝       ╚═════╝ ╚══════╝  ╚═══╝  
        Copyright © 2024 - 2025 NoobMCGaming
*/    

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use Noob\{PlayerShop};
use Noob\forms\PlayerShopForm;
use Noob\inventory\PlayerShopMenu;
use pocketmine\Server;

class PlayerShopCommand extends Command implements PluginOwned{
    private PlayerShop $plugin;
    public string $prefix = "§l§eHệ Thống§6 > ";

    public function __construct(PlayerShop $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("pshop", "Cửa Hàng Của Người Chơi", null, ["playershop"]);
        $this->setPermission("playershop.cmd");
    }

    public function execute(CommandSender $player, string $label, array $args){
        if (!$player instanceof Player) {
            $this->getOwningPlugin()->getLogger()->notice("Xin hãy sử dụng lệnh trong trò chơi");
            return 1;
        }
        $form = new PlayerShopForm;
        $inventory = new PlayerShopMenu;
        if(count($args) < 1 || count($args) > 2){
            $player->sendMessage($this->prefix . $this->plugin->getMessage("addItem"));
            $player->sendMessage($this->prefix . $this->plugin->getMessage("removeitem"));
            $player->sendMessage($this->prefix . $this->plugin->getMessage("open-playershop"));
            $player->sendMessage($this->prefix . $this->plugin->getMessage("myshop"));
        }
        else{
            switch($args[0]){
                case "additem":
                    if(isset($args[0])){
                        if(is_numeric($args[1])){
                            if($this->plugin->canSellItem($player)){
                                $item = $player->getInventory()->getItemInHand();
                                $itemName = $item->getCustomName();
                                if(!$item->isNull()){
                                    $this->plugin->addItem($player, (float)$args[1]);
                                    $player->sendMessage($this->prefix . "§fThêm Vật Phẩm Thành Công !");
                                    if($itemName == null || $itemName == ""){
                                        $itemName = $item->getVanillaName();
                                    }
                                    Server::getInstance()->broadcastMessage($this->prefix . "§f" . $player->getName() . " Đã Bán ". $item->getCount() . " ". $itemName . " §fVới Giá ". $args[1] . " Coin");
                                    $player->getInventory()->remove($item);
                                }
                                else{
                                    $player->sendMessage($this->prefix . $this->plugin->getMessage("item-null"));
                                }
                            }
                            else{
                                $player->sendMessage($this->prefix . $this->plugin->getMessage("cannot-additem"));
                            }
                        }
                        else{
                            $player->sendMessage($this->prefix . $this->plugin->getMessage("addItem"));
                        }
                    }
                    else{
                        $player->sendMessage($this->prefix . $this->plugin->getMessage("addItem"));
                    }
                break;
                case "removeitem":
                    if(isset($args[0])){
                        $form->deleteItem($player);
                    }
                    else{
                        $player->sendMessage($this->prefix . $this->plugin->getMessage("removeitem"));
                    }
                break;
                case "myshop":
                    if(isset($args[0])){
                        $inventory->myShop($player);
                    }
                    else{
                        $player->sendMessage($this->prefix . $this->plugin->getMessage("myshop"));  
                    }
                break;
                case "open":
                    if(isset($args[0])){
                        if(isset($args[1])){
                            if($this->plugin->getDataManager()->exists($args[1])){
                                $inventory->openShop($player, $args[1]);
                            }
                            else{
                                $player->sendMessage($this->prefix . $this->plugin->getMessage("No-Have-Shop"));
                            }
                        }
                        else{
                            $player->sendMessage($this->prefix . $this->plugin->getMessage("open-playershop"));
                        }
                    }
                    else{
                        $player->sendMessage($this->prefix . $this->plugin->getMessage("open-playershop"));
                    }
                break;
            }
        }
    }

    public function getOwningPlugin(): PlayerShop{
        return $this->plugin;
    }
}