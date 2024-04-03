<?php

namespace Valres\MineSystem\zones;

use pocketmine\item\StringToItemParser;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\Position;
use Valres\MineSystem\Main;

class ZoneManager
{
    /** @var Zone[] */
    public array $zones = [];

    /**
     * @param string $name
     * @return Zone|null
     */
    public function getZone(string $name): ?Zone
    {
        return $this->zones[$name] ?? null;
    }

    /**
     * @param Position $position
     * @return Zone|null
     */
    public function getZoneByPosition(Position $position): ?Zone
    {
        foreach($this->zones as $zone){
            if($zone->inZone($position)) return $zone;
        }
        return null;
    }

    /**
     * @return void
     */
    public function loadZones(): void
    {
        $config = Main::getInstance()->getConfig();


        foreach($config->getAll() as $name => ["world" => $worldname, "zones" => ["min" => $min, "max" => $max], "allowed-blocks" => $allowed, "new-blocks" => $new, "timer" => $timer]){
            $allowed_ = [];
            foreach($allowed as $_allowed){
                $allowed_[] = StringToItemParser::getInstance()->parse($_allowed)->getBlock()->getTypeId();
            }
            Server::getInstance()->getWorldManager()->loadWorld($worldname);
            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldname);
            for($x = $min[0];$x <= $max[0];$x++){
                for($z = $min[2];$z <= $max[2];$z++){
                    $world->loadChunk($x, $z);
                }
            }

            $this->zones[$name] = new Zone(
                $name,
                $world,
                new Vector3($min[0], $min[1], $min[2]),
                new Vector3($max[0], $max[1], $max[2]),
                $allowed_,
                $new,
                $timer
            );
        }
    }
}
