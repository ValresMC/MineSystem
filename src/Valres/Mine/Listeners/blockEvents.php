<?php

namespace Valres\Mine\Listeners;

use IvanCraft623\RankSystem\RankSystem;
use pocketmine\block\CoalOre;
use pocketmine\block\CopperOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\EmeraldOre;
use pocketmine\block\GoldOre;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\scheduler\ClosureTask;
use Valres\Mine\Mine;

class blockEvents implements Listener
{
    public function __construct(public Mine $plugin) {}

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreakOre(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $config = $this->plugin->config();
        if($player->getWorld()->getFolderName() === $config->get("world")){
            if($block instanceof GoldOre or $block instanceof CoalOre or $block instanceof DiamondOre or $block instanceof IronOre or $block instanceof CopperOre or $block instanceof EmeraldOre or $block instanceof LapisOre or $block instanceof RedstoneOre)
            {
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
                    $player->getWorld()->setBlock($block->getPosition(), VanillaBlocks::BEDROCK());
                }), 2);
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
                    $rand = [
                        VanillaBlocks::COPPER_ORE(),
                        VanillaBlocks::COAL_ORE(),
                        VanillaBlocks::IRON_ORE(),
                        VanillaBlocks::LAPIS_LAZULI_ORE(),
                        VanillaBlocks::REDSTONE_ORE(),
                        VanillaBlocks::DIAMOND_ORE(),
                        VanillaBlocks::EMERALD_ORE(),
                        VanillaBlocks::GOLD_ORE(),
                    ];
                    $player->getWorld()->setBlock($block->getPosition(), $rand[array_rand($rand)]);
                }), $config->get("regen-time")*20);
            } else {
                if(!(RankSystem::getInstance()->getSessionManager()->get($player)->hasPermission("mine.bypass"))){
                    $event->cancel();
                    foreach($event->getDrops() as $drop){
                        $drop->setCount(0);
                    }
                    $player->sendPopup($config->get("no-break-message"));
                }
            }
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $config = $this->plugin->config();
        if(!(RankSystem::getInstance()->getSessionManager()->get($player)->hasPermission("mine.bypass"))){
            if($player->getWorld()->getFolderName() === $config->get("world")){
                $event->cancel();
                $player->sendPopup($config->get("no-place-message"));
            }
        }
    }
}
