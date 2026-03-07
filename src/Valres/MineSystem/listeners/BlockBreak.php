<?php

declare(strict_types=1);

namespace Valres\MineSystem\listeners;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use Valres\MineSystem\Main;
use Valres\MineSystem\zones\Zone;

final class BlockBreak implements Listener {
    public function onBreak(BlockBreakEvent $event): void {
        $plugin = Main::getInstance();
        $block = $event->getBlock();
        $position = $block->getPosition();

        $zone = $plugin->zoneManager->getZoneByPosition($position);
        if (!$zone instanceof Zone) {
            return;
        }

        if (!in_array($block->getTypeId(), $zone->getAllowedBlocks(), true)) {
            return;
        }

        if ($plugin->cacheManager->isInCache($zone, $position)) {
            $event->cancel();
            return;
        }

        $position->getWorld()->setBlock($position, VanillaBlocks::BEDROCK());
        $plugin->cacheManager->inCache($zone, $position);
        $plugin->mineRegenerationManager->scheduleRegeneration($zone, $position);
    }
}
