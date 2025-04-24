<?php

declare(strict_types=1);

namespace wavycraft\wavyshop\form;

use pocketmine\player\Player;

use pocketmine\Server;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

use pocketmine\item\StringToItemParser;

use wavycraft\wavyshop\WavyShop;

use wavycraft\wavyeconomy\api\WavyEconomyAPI;

use terpz710\pocketforms\SimpleForm;
use terpz710\pocketforms\ModalForm;
use terpz710\pocketforms\CustomForm;

final class ShopForm {
    use SingletonTrait;

    protected array $shopData;

    public function __construct() {
        $config = new Config(WavyShop::getInstance()->getDataFolder() . "shop.yml", Config::YAML);
        $this->shopData = $config->getAll();
    }

    public function sendMainShopForm(Player $player) : void{
        $form = (new SimpleForm())
            ->setTitle("Shop")
            ->setContent("Select a category:");

        foreach ($this->shopData as $category => $info) {
            $form->addButton($category, 0, $info["image"]);
        }

        $form->setCallback(function(Player $player, $data) {
            if ($data === null) return;
            $categories = array_keys($this->shopData);
            $this->sendCategoryForm($player, $categories[$data]);
        });

        $player->sendForm($form);
    }

    public function sendCategoryForm(Player $player, string $category) : void{
        if (!isset($this->shopData[$category])) return;

        $form = (new SimpleForm())
            ->setTitle($category)
            ->setContent("Select an item to buy:");

        foreach ($this->shopData[$category]["items"] as $item) {
            $form->addButton($item["name"] . "\n§a$" . $item["price"], 0, $item["item_image"]);
        }

        $form->setCallback(function(Player $player, $data) use ($category) {
            if ($data === null) return;
            $item = $this->shopData[$category]["items"][$data];
            $this->sendAmountInputForm($player, $item);
        });

        $player->sendForm($form);
    }

    public function sendAmountInputForm(Player $player, array $itemData) : void{
        $form = (new CustomForm())
            ->setTitle("Buy " . $itemData["name"])
            ->addLabel("Price: $" . $itemData["price"] . "§f each")
            ->addInput("Enter quantity to buy:", "amount");

        $form->setCallback(function(Player $player, $data) use ($itemData) {
            if ($data === null || !isset($data[0]) || trim($data[0]) === "" || !is_numeric($data[0])) {
                $player->sendMessage("§cPlease enter a valid number!");
                return;
            }
            $amount = (int) $data[0];
            if ($amount <= 0) return;

            $this->sendConfirmationForm($player, $itemData, $amount);
        });

        $player->sendForm($form);
    }

    public function sendConfirmationForm(Player $player, array $itemData, int $amount) : void{
        $total = $itemData["price"] * $amount;

        $form = (new ModalForm())
            ->setTitle("Confirm Purchase")
            ->setContent("Buy §e{$amount}x {$itemData['name']}§f for §a$" . $total . "§f?")
            ->setButton1("Yes")
            ->setButton2("No");

        $form->setCallback(function(Player $player, bool $result) use ($itemData, $amount, $total) {
            if ($result) {
                $money = WavyEconomyAPI::getInstance()->getBalance($player->getName());
                if ($money >= $total) {
                    WavyEconomyAPI::getInstance()->removeMoney($player->getName(), $total);

                    $item = StringToItemParser::getInstance()->parse($itemData["id"]);
                    $item->setCount($amount);
                    $player->getInventory()->addItem($item);
                    $player->sendMessage("§aPurchased {$amount}x {$itemData['name']} for \${$total}");
                } else {
                    $player->sendMessage("§cYou do not have enough money!");
                }
            } else {
                $player->sendMessage("§ePurchase cancelled!");
            }
        });

        $player->sendForm($form);
    }
}
