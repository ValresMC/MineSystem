<?php

namespace Valres\Mine\Listener;

use Exception;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\scheduler\ClosureTask;
use Valres\Mine\Main;

class blockEvents implements Listener
{
    public function __construct(public Main $plugin) {}

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreakOre(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $config = $this->plugin->config();

        if(strtolower($player->getWorld()->getFolderName()) === strtolower($config->get("world"))){
            if (in_array($block->getTypeId(), [BlockTypeIds::COPPER_ORE, BlockTypeIds::COAL_ORE, BlockTypeIds::IRON_ORE, BlockTypeIds::GOLD_ORE, BlockTypeIds::DIAMOND_ORE, BlockTypeIds::LAPIS_LAZULI_ORE, BlockTypeIds::REDSTONE_ORE, BlockTypeIds::EMERALD_ORE]))
            {
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
                    $player->getWorld()->setBlock($block->getPosition(), VanillaBlocks::BEDROCK());
                }), 2);
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
                    $newblock = $this->chooseBlock();
                    $player->getWorld()->setBlock($block->getPosition(), StringToItemParser::getInstance()->parse($newblock)->getBlock());
                }), $config->get("regen-time")*20);
            } else {
                if(!($player->hasPermission("mine.bypass"))){
                    $event->setDrops([]);
                    $player->sendPopup($config->get("no-break-message"));
                    $event->cancel();
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
        if(!($player->hasPermission("mine.bypass"))){
            if($player->getWorld()->getFolderName() === $config->get("world")){
                $event->cancel();
                $player->sendPopup($config->get("no-place-message"));
            }
        }
    }

    /**
     * @throws Exception
     */
    public function pourcentage(array $blocks)
    {

        $total = array_sum($blocks);
        if ($total !== 100) {
            throw new Exception("Les pourcentages totaux ne sont pas égaux à 100.");
        }

        $chance = rand(1, 100);
        $cumul = 0;
        foreach ($blocks as $block => $pourcentage) {
            $cumul += $pourcentage;
            if($chance <= $cumul){
                return $block;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function chooseBlock(): string
    {
        $config = $this->plugin->config();
        return $this->pourcentage($config->get("blocks"));
    }


}
