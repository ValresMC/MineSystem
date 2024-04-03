<?php

namespace Valres\MineSystem\zones;

use JsonException;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\Position;
use Valres\MineSystem\Main;

class CacheManager
{
    public array $cache = [];

    public function inCache(Zone $zone, Position $position): void
    {
        $key = $position->x . ":" . $position->y . ":" . $position->z;

        $this->cache[$zone->getName()][] = $key;
    }

    public function outCache(Zone $zone, Position $position): void
    {
        $key = $position->x . ":" . $position->y . ":" . $position->z;

        if(isset($this->cache[$zone->getName()][$key])){
            unset($this->cache[$zone->getName()][$key]);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function saveCache(): void
    {
        $plugin = Main::getInstance();
        $data = $plugin->files("data");

        foreach($this->cache as $zone => $pos){
            $data->set($zone, $pos);
        }
        $data->save();
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function regenerateBlocks(): void
    {
        $plugin = Main::getInstance();
        $data = $plugin->files("data");

        foreach($data->getAll() as $zone => $pos){
            $zone = $plugin->zoneManager->getZone($zone);
            if(!$zone instanceof Zone) return;

            foreach($pos as $position){
                $pos_ = explode(":", $position);
                $position_ = new Position($pos_[0], $pos_[1], $pos_[2], $zone->getWorld());

                $zone->getWorld()->setBlock($position_, StringToItemParser::getInstance()->parse($this->chooseBlock($zone))->getBlock());
            }

            $data->remove($zone->getName());
        }
        $data->save();
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
