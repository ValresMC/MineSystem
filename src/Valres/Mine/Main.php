<?php

namespace Valres\Mine;

use JsonException;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Valres\Mine\Listeners\blockEvents;

class Main extends PluginBase
{
    use SingletonTrait;

    /**
     * @return void
     */
    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->saveResource("data.yml");
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
        foreach($config->get("allowed-blocks") as $allowed){
            $allowed_ = StringToItemParser::getInstance()->parse($allowed)->getBlock()->getTypeId();
            if(!isset(blockEvents::$allowed[$allowed_])){
                blockEvents::$allowed[] = $allowed_;
            }
        }
        blockEvents::$world = $config->get("world");
        blockEvents::$timer = $config->get("regen-time");

        $this->getServer()->getWorldManager()->loadWorld(blockEvents::$world);
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(){
            $world = $this->getServer()->getWorldManager()->getWorldByName(blockEvents::$world);
            $data = new Config($this->getDataFolder() . "data.yml", Config::YAML);
            foreach($data->getAll() as $pos => $block){
                $pos_ = explode(":", $pos);
                $world->setBlockAt($pos_[0], $pos_[1], $pos_[2], StringToItemParser::getInstance()->parse(blockEvents::chooseBlock(blockEvents::$blocks))->getBlock());
                $data->remove($pos);
            }
            $data->save();
        }), 20);

    }

    /**
     * @return void
     * @throws JsonException
     */
    protected function onDisable(): void
    {
        $data = new Config($this->getDataFolder() . "data.yml", Config::YAML);

        foreach(blockEvents::$data as $pos => $block){
            $data->set($pos, $block);
        }
        $data->save();
    }

    /**
     * @return void
     */
    protected function onLoad(): void
    {
        self::setInstance($this);
    }
}
