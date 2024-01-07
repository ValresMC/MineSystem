<?php

namespace Valres\Mine\Listeners;

use Exception;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;
use Valres\Mine\Main;

class blockEvents implements Listener
{
    public static string $world;
    public static array $messages = [];
    public static int $timer;
    public static array $blocks = [];


    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreakOre(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $plugin = Main::getInstance();

        if(strtolower($player->getWorld()->getFolderName()) === strtolower(self::$world)){
            if (in_array($block->getTypeId(), [BlockTypeIds::COPPER_ORE, BlockTypeIds::COAL_ORE, BlockTypeIds::IRON_ORE, BlockTypeIds::GOLD_ORE, BlockTypeIds::DIAMOND_ORE, BlockTypeIds::LAPIS_LAZULI_ORE, BlockTypeIds::REDSTONE_ORE, BlockTypeIds::EMERALD_ORE]))
            {
                $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
                    $player->getWorld()->setBlock($block->getPosition(), VanillaBlocks::BEDROCK());
                }), 2);
                $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
                    if($player->getWorld()->getBlock($block->getPosition())->getTypeId() === BlockTypeIds::BEDROCK){
                        $player->getWorld()->setBlock($block->getPosition(), StringToItemParser::getInstance()->parse($this->chooseBlock(self::$blocks))->getBlock());
                    }
                }), self::$timer*20);
            } else {
                if(!($player->hasPermission("mine.bypass"))){
                    $event->cancel();
                    $event->setDrops([]);
                    $player->sendPopup(self::$messages["no-break-message"]);
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

        if(!($player->hasPermission("mine.bypass"))){
            if(strtolower($player->getWorld()->getFolderName()) === strtolower(self::$world)){
                $event->cancel();
                $player->sendPopup(self::$messages["no-place-message"]);
            }
        }
    }

    public function chooseBlock(array $blocks)
    {
        $rand = mt_rand(1, (int) array_sum($blocks));

        foreach ($blocks as $block => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $block;
            }
        }
    }
}
