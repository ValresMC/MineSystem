<?php

declare(strict_types=1);

namespace Valres\MineSystem\zones;

use pocketmine\item\StringToItemParser;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use Valres\MineSystem\Main;

final class ZoneManager {
    /** @var array<string, Zone> */
    private array $zones = [];

    public function __construct(
        private Main $plugin
    ) {
    }

    public function getZone(string $name): ?Zone {
        return $this->zones[$name] ?? null;
    }

    public function getZoneByPosition(Position $position): ?Zone {
        foreach ($this->zones as $zone) {
            if ($zone->inZone($position)) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * @return array<string, Zone>
     */
    public function getZones(): array {
        return $this->zones;
    }

    public function loadZones(): void {
        $config = $this->plugin->getConfig();
        $worldManager = $this->plugin->getServer()->getWorldManager();

        foreach ($config->getAll() as $name => $data) {
            if (!is_array($data)) {
                $this->plugin->getLogger()->warning("La zone {$name} n'est pas un tableau valide.");
                continue;
            }

            $worldName = $data['world'] ?? null;
            $zoneData = $data['zones'] ?? null;
            $allowed = $data['allowed-blocks'] ?? [];
            $newBlocks = $data['new-blocks'] ?? [];
            $timer = (int) ($data['timer'] ?? 30);

            if (!is_string($worldName) || !is_array($zoneData) || !is_array($allowed) || !is_array($newBlocks)) {
                $this->plugin->getLogger()->warning("Configuration invalide pour la zone {$name}.");
                continue;
            }

            $min = $zoneData['min'] ?? null;
            $max = $zoneData['max'] ?? null;

            if (!is_array($min) || !is_array($max) || count($min) !== 3 || count($max) !== 3) {
                $this->plugin->getLogger()->warning("Les coordonnées min/max de la zone {$name} sont invalides.");
                continue;
            }

            $worldManager->loadWorld($worldName);
            $world = $worldManager->getWorldByName($worldName);
            if ($world === null) {
                $this->plugin->getLogger()->warning("Le monde {$worldName} est introuvable pour la zone {$name}.");
                continue;
            }

            $allowedTypeIds = [];
            foreach ($allowed as $blockName) {
                $item = StringToItemParser::getInstance()->parse((string) $blockName);
                if ($item === null) {
                    $this->plugin->getLogger()->warning("Bloc invalide dans allowed-blocks pour la zone {$name}: {$blockName}");
                    continue;
                }

                $allowedTypeIds[] = $item->getBlock()->getTypeId();
            }

            $minX = min((int) $min[0], (int) $max[0]);
            $minY = min((int) $min[1], (int) $max[1]);
            $minZ = min((int) $min[2], (int) $max[2]);
            $maxX = max((int) $min[0], (int) $max[0]);
            $maxY = max((int) $min[1], (int) $max[1]);
            $maxZ = max((int) $min[2], (int) $max[2]);

            for ($chunkX = $minX >> 4; $chunkX <= ($maxX >> 4); $chunkX++) {
                for ($chunkZ = $minZ >> 4; $chunkZ <= ($maxZ >> 4); $chunkZ++) {
                    $world->loadChunk($chunkX, $chunkZ);
                }
            }

            $this->zones[$name] = new Zone(
                (string) $name,
                $world,
                new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ),
                $allowedTypeIds,
                $newBlocks,
                max(1, $timer)
            );
        }
    }
}
