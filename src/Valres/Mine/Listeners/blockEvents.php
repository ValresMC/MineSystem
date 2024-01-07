<?php

namespace Valres\Mine\Listeners;

use Exception;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;
use Valres\Mine\Main;

class blockEvents implements Listener
{
    public static string $world;
    public static array $messages = [];
    public static int $timer;
    public static array $blocks = [];
    public static array $allowed = [];
    public static array $data = [];


    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreakOre(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();

        if(strtolower($player->getWorld()->getFolderName()) === strtolower(self::$world)){
            if(in_array($block->getTypeId(), self::$allowed)){
                $this->scheduleBlockChange($player, $block);
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
     * @param Player $player
     * @param Block $block
     * @return void
     */
    private function scheduleBlockChange(Player $player, Block $block): void
    {
        $plugin = Main::getInstance();

        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
            $player->getWorld()->setBlock($block->getPosition(), VanillaBlocks::BEDROCK());
            self::$data[$block->getPosition()->x . ":" . $block->getPosition()->y . ":" . $block->getPosition()->z] = $block->getName();
        }), 2);
        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $block){
            $this->replaceBedrock($player, $block);
        }), self::$timer*20);
    }

    /**
     * @param Player $player
     * @param Block $block
     * @return void
     */
    private function replaceBedrock(Player $player, Block $block): void
    {
        if($player->getWorld()->getBlock($block->getPosition())->getTypeId() === BlockTypeIds::BEDROCK){
            $player->getWorld()->setBlock($block->getPosition(), StringToItemParser::getInstance()->parse(self::chooseBlock(self::$blocks))->getBlock());
            $key = $block->getPosition()->x . ":" . $block->getPosition()->y . ":" . $block->getPosition()->z;
            if(isset(self::$data[$key])){
                unset(self::$data[$key]);
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

    public static function chooseBlock(array $blocks)
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
