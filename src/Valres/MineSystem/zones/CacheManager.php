<?php

declare(strict_types=1);

namespace Valres\MineSystem\zones;

use JsonException;
use pocketmine\item\StringToItemParser;
use pocketmine\world\Position;
use Valres\MineSystem\Main;
use Valres\MineSystem\utils\WeightedBlockPicker;

final class CacheManager {
    /** @var array<string, array<string, true>> */
    private array $cache = [];

    public function __construct(
        private readonly Main       $plugin,
        private readonly WeightedBlockPicker $picker
    ) {
    }

    public function isInCache(Zone $zone, Position $position): bool {
        return isset($this->cache[$zone->getName()][$this->posToKey($position)]);
    }

    public function inCache(Zone $zone, Position $position): void {
        $this->cache[$zone->getName()][$this->posToKey($position)] = true;
    }

    public function outCache(Zone $zone, Position $position): void {
        unset($this->cache[$zone->getName()][$this->posToKey($position)]);

        if (isset($this->cache[$zone->getName()]) && empty($this->cache[$zone->getName()])) {
            unset($this->cache[$zone->getName()]);
        }
    }

    /**
     * @throws JsonException
     */
    public function saveCache(): void {
        $data = $this->plugin->files('data');
        $data->setAll([]);

        foreach ($this->cache as $zoneName => $positions) {
            $data->set($zoneName, array_keys($positions));
        }

        $data->save();
    }

    /**
     * @throws JsonException
     */
    public function regenerateBlocks(): void {
        $data = $this->plugin->files('data');

        foreach ($data->getAll() as $zoneName => $positions) {
            $zone = $this->plugin->zoneManager->getZone((string) $zoneName);
            if (!$zone instanceof Zone) {
                $this->plugin->getLogger()->warning("Zone introuvable dans data.yml: " . (string) $zoneName);
                continue;
            }

            if (!is_array($positions)) {
                $data->remove($zone->getName());
                continue;
            }

            foreach ($positions as $rawPosition) {
                if (!is_string($rawPosition)) {
                    continue;
                }

                $parts = explode(':', $rawPosition);
                if (count($parts) !== 3) {
                    continue;
                }

                $position = new Position((int) $parts[0], (int) $parts[1], (int) $parts[2], $zone->getWorld());
                $blockName = $this->picker->pick($zone->getNewBlocks());
                if ($blockName === null) {
                    continue;
                }

                $item = StringToItemParser::getInstance()->parse($blockName);
                if ($item === null) {
                    $this->plugin->getLogger()->warning("Bloc invalide dans new-blocks pour la zone {$zone->getName()}: {$blockName}");
                    continue;
                }

                $zone->getWorld()->setBlock($position, $item->getBlock());
            }

            $data->remove($zone->getName());
        }

        $data->save();
        $this->cache = [];
    }

    private function posToKey(Position $position): string {
        return $position->getFloorX() . ':' . $position->getFloorY() . ':' . $position->getFloorZ();
    }
}
