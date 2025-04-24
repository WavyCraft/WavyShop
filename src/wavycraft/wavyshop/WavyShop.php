<?php 

declare(strict_types=1);

namespace wavycraft\wavyshop;

use pocketmine\plugin\PluginBase;

use wavycraft\wavyshop\command\ShopCommand;

use CortexPE\Commando\PacketHooker;

class WavyShop extends PluginBase {

    protected static self $instance;

    protected function onLoad() : void{
        self::$instance = $this;
    }

    protected function onEnable() : void{
        $this->saveResource("shop.yml");

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->getServer()->getCommandMap()->register("WavyShop", new ShopCommand($this, "shop", "Opens shop menu"));
    }

    public static function getInstance() : self{
        return self::$instance;
    }
}