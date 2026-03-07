<?php

declare(strict_types=1);

namespace Valres\MineSystem\zones;

use pocketmine\block\BlockTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use Valres\MineSystem\Main;
use Valres\MineSystem\utils\WeightedBlockPicker;

final readonly class MineRegenerationManager {
    public function __construct(
        private Main                $plugin,
        private WeightedBlockPicker $picker
    ) {
    }

    public function scheduleRegeneration(Zone $zone, Position $position): void {
        $this->plugin->getScheduler()->scheduleDelayedTask(
            new ClosureTask(function() use ($zone, $position): void {
                $world = $position->getWorld();

                if ($world->getBlock($position)->getTypeId() !== BlockTypeIds::BEDROCK) {
                    return;
                }

                $blockName = $this->picker->pick($zone->getNewBlocks());
                if ($blockName === null) {
                    $this->plugin->getLogger()->warning("Aucun bloc valide n'a été trouvé pour la zone {$zone->getName()}");
                    return;
                }

                $item = StringToItemParser::getInstance()->parse($blockName);
                if ($item === null) {
                    $this->plugin->getLogger()->warning("Bloc invalide dans new-blocks pour la zone {$zone->getName()}: {$blockName}");
                    return;
                }

                $world->setBlock($position, $item->getBlock());
                $this->plugin->cacheManager->outCache($zone, $position);
            }),
            $zone->getTimer() * 20
        );
    }
}
