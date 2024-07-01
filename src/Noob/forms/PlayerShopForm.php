<?php

namespace Noob\forms;

/*
███╗   ██╗██╗  ██╗██╗   ██╗████████╗    ██████╗ ███████╗██╗   ██╗
████╗  ██║██║  ██║██║   ██║╚══██╔══╝    ██╔══██╗██╔════╝██║   ██║
██╔██╗ ██║███████║██║   ██║   ██║       ██║  ██║█████╗  ██║   ██║
██║╚██╗██║██╔══██║██║   ██║   ██║       ██║  ██║██╔══╝  ╚██╗ ██╔╝
██║ ╚████║██║  ██║╚██████╔╝   ██║       ██████╔╝███████╗ ╚████╔╝ 
╚═╝  ╚═══╝╚═╝  ╚═╝ ╚═════╝    ╚═╝       ╚═════╝ ╚══════╝  ╚═══╝  
        Copyright © 2024 - 2025 NoobMCGaming
*/   

use jojoe77777\FormAPI\ModalForm;
use pocketmine\{Server, player\Player};
use Noob\PlayerShop;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use Noob\CoinAPI;
use Noob\Libs;

class PlayerShopForm {

    public string $prefix = "§l§eHệ Thống§6 > ";

    public function deleteItem(Player $player){
        $button = [];
        $name_dropdown = [];
        foreach(PlayerShop::getInstance()->getData($player) as $data => $value){
            $button[] = $value;
            $ex = explode(":", $value);
            $item = Libs::getInstance()->dataToItem($ex[1]);
            $itemName = $item->getCustomName();
            if($itemName == null || $itemName == ""){
            	$itemName = $item->getVanillaName();
            }
            $name_dropdown[] = $itemName;
        }
        $form = new CustomForm(function(Player $player, $data) use ($button){
            if($data === null){
                return true;
            }
            $category = $button[$data[0]];
            $ex = explode(":", $category);
            if($player->getInventory()->canAddItem(Libs::getInstance()->dataToItem($ex[1]))){
                $player->getInventory()->addItem(Libs::getInstance()->dataToItem($ex[1]));
            }
            else{
                $player->sendMessage($this->prefix . "§6Bạn Cần Lấy Ít Nhất 1 Ô Trống !"); 
            }
            PlayerShop::getInstance()->removeItem($player, $ex[1]);
            $player->sendMessage($this->prefix . "§6Đã Gỡ Vật Phẩm Thành Công !"); 
        });
        $form->setTitle("§l§3● §2Xóa Vật Phẩm §3●");
        $form->addDropdown("§l§c● §eChọn Vật Phẩm Muốn Xóa:", $name_dropdown, 0);
        $form->sendToPlayer($player);
    }
}