<?php

namespace xenialdan\RedSkyBlockChallenges\Commands;

use muqsit\invmenu\InvMenu;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use RedCraftPE\RedSkyBlock\SkyBlock;
use xenialdan\RedSkyBlockChallenges\Challenge;
use xenialdan\RedSkyBlockChallenges\Loader;

class Island extends PluginCommand
{

    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);
        $this->setAliases(["isc", "islandc", "ischallenges"]);
        $this->setPermission("skyblock.island.challenges");
        $this->setDescription("This is the main command for RedSkyBlockChallenges.");
        $this->setUsage("/isc");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender->hasPermission("skyblock.is")) {
            $skyblockArray = SkyBlock::getInstance()->skyblock->get("SkyBlock", []);

            if ($sender->hasPermission("skyblock.island.challenges")) {

                if (array_key_exists(strtolower($sender->getName()), $skyblockArray)) {

                    $this->createChallengesInventory($sender);
                    $sender->sendMessage(TextFormat::GREEN . "Island Challenges Menu Opened.");
                    return true;
                } else {

                    $sender->sendMessage(TextFormat::RED . "You do not have an island yet.");
                    return true;
                }
            } else {

                $sender->sendMessage(TextFormat::RED . "You do not have the proper permissions to run this command.");
                return true;
            }
        } else {
            $sender->sendMessage(TextFormat::RED . "You do not have the proper permissions to run this command.");
            return true;
        }
    }

    public function createChallengesInventory(Player $player)
    {

        $skyblockArray = SkyBlock::getInstance()->skyblock->get("SkyBlock", []);
        $name = strtolower($player->getName());

        $menu = InvMenu::create(InvMenu::TYPE_CHEST)->setName(TextFormat::RED . $skyblockArray[$name]["Name"] . " Challenges:")->readonly();
        /**
         * @var string $category
         * @var Challenge $challenge
         */
        foreach (Loader::$challenges as $category => $challenges) {
            foreach ($challenges as $challenge) {
                $menu->getInventory()->addItem($challenge->getFullIconItem());
            }
        }
        $menu->setListener(function (Player $player, Item $clicked, Item $clickedWith, SlotChangeAction $action) use($skyblockArray): bool {
            /**
             * @var string $category
             * @var Challenge $challenge
             */
            foreach (Loader::$challenges as $category => $challenges) {
                foreach ($challenges as $challenge) {
                    if ($challenge->getTitle() === $clicked->getCustomName()) {
                        switch ($challenge->getType()){
                            case Loader::CHALLENGE_TYPE_ITEM:{
                                $contains = true;
                                foreach ($challenge->getRequiredItems() as $requiredItem) {
                                    if ($contains && !$player->getInventory()->contains($requiredItem)) $contains = false;
                                }
                                if ($contains) {
                                    $player->getInventory()->removeItem(...$challenge->getRequiredItems());
                                    $player->getInventory()->addItem(...$challenge->getRewardItems());
                                    $name = strtolower($player->getName());
                                    $skyblockArray[$name]["Value"]+=$challenge->getValue();
                                    SkyBlock::getInstance()->skyblock->set("SkyBlock", $skyblockArray);
                                    SkyBlock::getInstance()->skyblock->save();
                                    $player->sendMessage(TextFormat::GREEN . "Successfully completed the " . $challenge->getTitle() . " challenge! Value: ".$challenge->getValue());
                                } else {
                                    $player->sendMessage(TextFormat::RED . "Not enough items for the " . $challenge->getTitle() . " challenge, need " . implode(", ", $challenge->getRequiredItems()));
                                }
                                break 3;
                            }
                        }
                    }
                }
            }
            return true;
        });
        $menu->send($player);
    }
}
