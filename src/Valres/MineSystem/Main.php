<?php

declare(strict_types=1);

namespace Valres\MineSystem;

use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Valres\MineSystem\listeners\BlockBreak;
use Valres\MineSystem\utils\WeightedBlockPicker;
use Valres\MineSystem\zones\CacheManager;
use Valres\MineSystem\zones\MineRegenerationManager;
use Valres\MineSystem\zones\ZoneManager;

final class Main extends PluginBase {
    use SingletonTrait;

    public ZoneManager $zoneManager;
    public CacheManager $cacheManager;
    public MineRegenerationManager $mineRegenerationManager;
    public WeightedBlockPicker $weightedBlockPicker;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    /** @throws JsonException */
    protected function onEnable(): void {
        $this->saveDefaultConfig();

        $this->weightedBlockPicker = new WeightedBlockPicker();
        $this->zoneManager = new ZoneManager($this);
        $this->cacheManager = new CacheManager($this, $this->weightedBlockPicker);
        $this->mineRegenerationManager = new MineRegenerationManager($this, $this->weightedBlockPicker);

        $this->zoneManager->loadZones();
        $this->cacheManager->regenerateBlocks();

        $this->getServer()->getPluginManager()->registerEvents(new BlockBreak(), $this);
    }

    /**
     * @throws JsonException
     */
    protected function onDisable(): void {
        $this->cacheManager->saveCache();
    }

    public function files(string $name): Config {
        return new Config($this->getDataFolder() . $name . '.yml', Config::YAML);
    }
}
