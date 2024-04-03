<?php

namespace Valres\MineSystem\listeners;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use Valres\MineSystem\Main;
use Valres\MineSystem\zones\Zone;

class BlockBreak implements Listener
{
    public function onBreak(BlockBreakEvent $event): void
    {
        $plugin = Main::getInstance();
        $block = $event->getBlock();
        $position = $block->getPosition();

        $zone = $plugin->zoneManager->getZoneByPosition($position);
        if(!$zone instanceof Zone) return;

        if(!in_array($block->getTypeId(), $zone->getAllowedBlocks())) return;

        $this->changeBlock($position);
    }

    public function changeBlock(Position $position): void
    {
        $plugin = Main::getInstance();

        $zone = $plugin->zoneManager->getZoneByPosition($position);
        if(!$zone instanceof Zone) return;

        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($plugin, $position, $zone): void{
            $position->getWorld()->setBlock($position, VanillaBlocks::BEDROCK());
            $plugin->cacheManager->inCache($zone, $position);
        }), 2);

        $this->replaceBlock($position);
    }

    public function replaceBlock(Position $position): void
    {
        $plugin = Main::getInstance();

        $zone = $plugin->zoneManager->getZoneByPosition($position);
        if(!$zone instanceof Zone) return;

        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($plugin, $zone, $position): void{
            if($position->getWorld()->getBlock($position)->getTypeId() === BlockTypeIds::BEDROCK){
                $position->getWorld()->setBlock($position, StringToItemParser::getInstance()->parse($this->chooseBlock($zone))->getBlock());
                $plugin->cacheManager->outCache($zone, $position);
            }
        }), $zone->getTimer() * 20);
    }

    public function chooseBlock(Zone $zone)
    {
        $blocks = $zone->getNewBlocks();
        $rand = mt_rand(1, (int) array_sum($blocks));

        foreach($blocks as $block => $percent){
            $rand -= $percent;
            if($rand <= 0){
                return $block;
            }
        }
    }
}
