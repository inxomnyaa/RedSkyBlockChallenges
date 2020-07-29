<?php

namespace xenialdan\RedSkyBlockChallenges;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use xenialdan\RedSkyBlockChallenges\Commands\Island;

class Loader extends PluginBase
{

    /** @var BaseLang */
    public static $lang;

    private static $instance;

    /** @var string[] */
    public static $challenges = [];
    public const CHALLENGE_TYPE_ITEM = "item";
    public const CHALLENGE_TYPE_BLOCK = "block";
    public const CHALLENGE_TYPE_ENTITY = "entity";
    public const CHALLENGE_TYPE_EVENT = "event";

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onLoad(): void
    {
        self::$instance = $this;
        self::$lang = new BaseLang(BaseLang::FALLBACK_LANGUAGE, $this->getFile() . "resources/");
        $this->getServer()->getCommandMap()->register("is", new Island("islandchallenges", $this));
    }

    public function onEnable(): void
    {
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        /**
         * @var string $path
         * @var \SplFileInfo $fileInfo
         */
        foreach ($this->getResources() as $path => $fileInfo) {
            if (!$fileInfo->isFile() || strpos($path, "challenges") === false) continue;
            [$challenges, $category, $file] = explode("/", $path);
            $c = new Challenge($fileInfo->getRealPath());
            if ($c->isDisabled()) continue;
            if ($c->getType() !== self::CHALLENGE_TYPE_ITEM) continue;//TODO reenable when coded
            self::$challenges[$category][basename($file, ".json")] = $c;
        }
    }
}
