<?php

namespace Noob\inventory;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\transaction\InvMenuTransaction;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use Noob\PlayerShop;
use Noob\Libs;
use Noob\CoinAPI;
use pocketmine\item\Tool;
use pocketmine\math\Vector3;

class PlayerShopMenu {

    public $prefix = "§l§eHệ Thống §6> ";
  
    public function myShop(Player $player): void{
        $menu = CustomSizedInvMenu::create(54);
        $menu->setName("Cửa Hàng Của Bạn");
        $inventory = $menu->getInventory();
        $slot = -1;
        $itemName = [];
        $slotCount = [];
        if(PlayerShop::getInstance()->getData($player) !== []){
            foreach(PlayerShop::getInstance()->getData($player) as $data => $value){
                $ex = explode(":", $value);
                $slot++;
                $slotCount[] = $slot;
                $item = Libs::getInstance()->dataToItem($ex[1]);
                $inventory->setItem($slot, $item);
                $itemNameOfItem = $item->getCustomName();
                if($itemNameOfItem == null || $itemNameOfItem == ""){
                    $itemNameOfItem = $item->getVanillaName();
                }
                $itemName[] = $itemNameOfItem;
            }
        }
        for($i = 0; $i < 54; $i++){
            if(!in_array($i, $slotCount)){
                $item = StringToItemParser::getInstance()->parse("red_stained_glass_pane")->setCustomName("§l§cÔ Này Chưa Được Bấm");
                $enchant = StringToEnchantmentParser::getInstance()->parse("unbreaking");
                $item->addEnchantment(new EnchantmentInstance($enchant, 1000));
                $inventory->setItem($i, $item);
            }
        }
        $menu->setListener(function(InvMenuTransaction $transaction) use ($player, $itemName): InvMenuTransactionResult{
            $itemClicked = $transaction->getItemClicked();
            $itemClickedWith = $transaction->getItemClickedWith();
            $action = $transaction->getAction();
            if(in_array($itemClicked->getName(), $itemName)){
                Libs::getInstance()->sendSound($player, "mob.enderdragon.flap");
                return $transaction->discard();
            }
            if($itemClicked->getName() == "§l§cÔ Này Chưa Được Bấm"){
                Libs::getInstance()->sendSound($player, "mob.horse.angry");
                return $transaction->discard();
            }
            return $transaction->continue();
            
        });
        $menu->send($player);
    }

    public function openShop(Player $player, string $playerName): void{
        $menu = CustomSizedInvMenu::create(54);
        $menu->setName("Cửa Hàng Của ". $playerName);
        $inventory = $menu->getInventory();
        $slot = -1;
        $itemName = [];
        $slotCount = [];
        $button = [];
        if(PlayerShop::getInstance()->getDataByName($playerName) !== []){
            foreach(PlayerShop::getInstance()->getDataByName($playerName) as $data => $value){
                $ex = explode(":", $value);
                $slot++;
                $slotCount[] = $slot;
                $item = Libs::getInstance()->dataToItem($ex[1]);
                $inventory->setItem($slot, $item);
                $itemNameOfItem = $item->getCustomName();
                if($itemNameOfItem == null || $itemNameOfItem == ""){
                    $itemNameOfItem = $item->getVanillaName();
                }
                $itemName[] = $itemNameOfItem;
                $button[] = $value;
            }
        }
        for($i = 0; $i < 54; $i++){
            if(!in_array($i, $slotCount)){
                $item = StringToItemParser::getInstance()->parse("red_stained_glass_pane")->setCustomName("§l§cÔ Này Chưa Được Bấm");
                $enchant = StringToEnchantmentParser::getInstance()->parse("unbreaking");
                $item->addEnchantment(new EnchantmentInstance($enchant, 1000));
                $inventory->setItem($i, $item);
            }
        }
        $menu->setListener(function(InvMenuTransaction $transaction) use ($player, $itemName, $button, $playerName): InvMenuTransactionResult{
            $itemClicked = $transaction->getItemClicked();
            $itemClickedWith = $transaction->getItemClickedWith();
            $action = $transaction->getAction();
            $slot = $action->getSlot();
            if($itemClicked->getName() == "§l§cÔ Này Chưa Được Bấm"){
                Libs::getInstance()->sendSound($player, "mob.horse.angry");
                return $transaction->discard();
            }
            else{
                $category = $button[$slot];
                $player->removeCurrentWindow();
                $this->confirm($player, $playerName, $category);
                return $transaction->discard();
            }
            return $transaction->continue();
        });
        $menu->send($player);
    }

    public function confirm(Player $player, string $playerName, string $encode): void{
        $menu = CustomSizedInvMenu::create(54);
        $menu->setName("Cửa Hàng Của Bạn");
        $inventory = $menu->getInventory();
        $slot = -1;
        $itemName = [];
        $ex = explode(":", $encode);
        $item = StringToItemParser::getInstance()->parse("player_head")->setCustomName("§l§6Giá Mua: ". (string)$ex[0]);
        $inventory->setItem(0, $item);
        $inventory->setItem(22, Libs::getInstance()->dataToItem($ex[1]));
        $inventory->setItem(20, StringToItemParser::getInstance()->parse("lime_stained_glass")->setCustomName("§l§aĐồng Ý"));
        $inventory->setItem(24, StringToItemParser::getInstance()->parse("red_stained_glass")->setCustomName("§l§cTừ Chối"));
        for($i = 0; $i < 54; $i++){
            if(!in_array($i, [0, 20, 22, 24])){
                $item = StringToItemParser::getInstance()->parse("gray_stained_glass_pane")->setCustomName("§r§7  ");
                $inventory->setItem($i, $item);
            }
        }
        $menu->setListener(function(InvMenuTransaction $transaction) use ($player, $ex, $playerName): InvMenuTransactionResult{
            $itemClicked = $transaction->getItemClicked();
            $itemClickedWith = $transaction->getItemClickedWith();
            $action = $transaction->getAction();
            if($itemClicked->getName() == "§l§aĐồng Ý"){
                if(PlayerShop::getInstance()->hasItem($playerName, $ex[1])){
                    if(CoinAPI::getInstance()->myCoin($player) >= (int)$ex[0]){
                        CoinAPI::getInstance()->reduceCoin($player, (int)$ex[0]);
                        CoinAPI::getInstance()->addCoinByName($playerName, (int)$ex[0]);
                        if($player->getInventory()->canAddItem(Libs::getInstance()->dataToItem($ex[1]))){
                            $player->getInventory()->addItem(Libs::getInstance()->dataToItem($ex[1]));
                            $player->sendMessage($this->prefix . "§6Mua Thành Công !"); 
                            PlayerShop::getInstance()->removeItemByName($playerName, $ex[1]);
                        }
                        else{
                            $player->sendMessage($this->prefix . "§6Bạn Cần Lấy Ít Nhất 1 Ô Trống !"); 
                        }
                    }
                    else{
                        $player->sendMessage($this->prefix . "§fBạn Không Đủ Coin !");
                    }
                }
                else{
                    $player->sendMessage($this->prefix . "§fRất Tiếc Vật Phẩm Đã Bị Mua Mất");
                }
                $player->removeCurrentWindow();
                Libs::getInstance()->sendSound($player, "random.levelup");
                return $transaction->discard();
            }
            if($itemClicked->getName() == "§l§cTừ Chối"){
                $player->removeCurrentWindow();
                Libs::getInstance()->sendSound($player, "random.levelup");
                return $transaction->discard();
            }
            if($itemClicked->getName() == "§r§7  "){
                Libs::getInstance()->sendSound($player, "mob.horse.angry");
                return $transaction->discard();
            }
            return $transaction->continue();
            
        });
        $menu->send($player);
    }
}