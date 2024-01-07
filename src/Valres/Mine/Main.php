<?php

namespace Valres\Mine;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Valres\Mine\Listeners\blockEvents;

class Main extends PluginBase
{
    use SingletonTrait;

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getLogger()->info("by Valres est lancé !");
        $this->getServer()->getPluginManager()->registerEvents(new blockEvents(), $this);

        $config = $this->getConfig();

        foreach($config->get("messages") as $type => $message){
            if(!isset(blockEvents::$messages[$type])){
                blockEvents::$messages[$type] = $message;
            }
        }
        foreach($config->get("blocks") as $block => $pourcent){
            if(!isset(blockEvents::$blocks[$block])){
                blockEvents::$blocks[$block] = $pourcent;
            }
        }
        blockEvents::$world = $config->get("world");
        blockEvents::$timer = $config->get("regen-time");
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }
}
