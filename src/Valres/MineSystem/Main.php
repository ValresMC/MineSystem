<?php

namespace Valres\MineSystem;

use JsonException;
use platz1de\EasyEdit\schematic\nbt\CompressedFileStream;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Valres\MineSystem\listeners\BlockBreak;
use Valres\MineSystem\trait\LoaderTrait;

class Main extends PluginBase
{
    use SingletonTrait, LoaderTrait;

    /**
     * @return void
     */
    protected function onEnable(): void
    {
        $this->loadAll();
        $this->zoneManager->loadZones();
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(): void{
            $this->cacheManager->regenerateBlocks();
        }), 10);


        $this->getServer()->getPluginManager()->registerEvents(new BlockBreak(), $this);
    }

    /**
     * @return void
     */
    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    /**
     * @return void
     * @throws JsonException
     */
    protected function onDisable(): void
    {
        $this->cacheManager->saveCache();
    }

    /**
     * @param string $name
     * @return Config
     */
    public function files(string $name): Config
    {
        return new Config($this->getDataFolder() . $name . ".yml", Config::YAML);
    }
}
