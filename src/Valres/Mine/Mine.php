<?php

namespace Valres\Mine;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Valres\Mine\Listeners\blockEvents;

class Mine extends PluginBase
{
    use SingletonTrait;

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getLogger()->info("by Valres est lancé !");
        $this->getServer()->getPluginManager()->registerEvents(new blockEvents($this), $this);
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    public function config(): Config
    {
        return new Config($this->getDataFolder()."config.yml", Config::YAML);
    }
}
