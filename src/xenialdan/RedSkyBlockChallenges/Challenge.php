<?php

namespace xenialdan\RedSkyBlockChallenges;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;

class Challenge extends Config
{

    public function isDisabled(): bool
    {
        return $this->get("disabled", 0) === 1;
    }

    public function getValue(): string
    {
        return $this->get("value", 10);
    }

/*
Available types:
- items
- mob_count
- block_nearby
- block_count
- level
- dimension
*/
    public function getType(): string
    {
        return $this->get("type", "item");
    }

    public function isSingleUse(): bool
    {
        return $this->get("singleuse", 0) === 1;
    }

    public function getRequiredItems(): array
    {
        $items = [];
        foreach ($this->get("items", []) as $item) {
            $items[] = ItemFactory::fromString(strval($item["item"] ?? "minecraft:air") . ":" . strval($item["data"] ?? "0"))->setCount(intval($item["count"] ?? 1));
        }
        return $items;
    }

    public function getRewardItems(): array
    {
        $items = [];
        foreach ($this->get("rewards", []) as $item) {
            $items[] = ItemFactory::fromString(strval($item["item"] ?? "minecraft:air") . ":" . strval($item["data"] ?? "0"))->setCount(intval($item["count"] ?? 1));
        }
        return $items;
    }

    public function getIconItem(): Item
    {
        $item = $this->get("icon", ["item" => "minecraft:air", "data" => 0]);
        return ItemFactory::fromString(strval($item["item"] ?? "minecraft:air") . ":" . strval($item["data"] ?? "0"));
    }

    public function getFullIconItem(): Item
    {
        $item = $this->getIconItem();
        return $item->setCustomName($this->getTitle())->setLore(explode(PHP_EOL, $this->getDescription()));
    }

    public function getTitle(): string
    {
        return Loader::$lang->translateString($this->get("title", ""));
    }

    public function getDescription(): string
    {
        return Loader::$lang->translateString($this->get("description", ""));
    }
}