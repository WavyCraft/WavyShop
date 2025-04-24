<?php

declare(strict_types=1);

namespace wavycraft\wavyshop\command;

use pocketmine\command\CommandSender;

use pocketmine\player\Player;

use wavycraft\wavyshop\form\ShopForm;

use CortexPE\Commando\BaseCommand;

class ShopCommand extends BaseCommand {

    protected function prepare() : void{
        $this->setPermission("wavyshop.cmd");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void{
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return;
        }

        ShopForm::getInstance()->sendMainShopForm($sender);
    }
}