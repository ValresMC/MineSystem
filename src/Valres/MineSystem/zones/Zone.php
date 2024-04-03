<?php

namespace Valres\MineSystem\zones;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;

class Zone
{
    public function __construct(
        protected string $name,
        protected World $world,
        protected Vector3 $min,
        protected Vector3 $max,
        protected array $allowed,
        protected array $newblock,
        protected int $timer
    ) {}

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }

    /**
     * @return Vector3
     */
    public function getMin(): Vector3
    {
        return $this->min;
    }

    /**
     * @return Vector3
     */
    public function getMax(): Vector3
    {
        return $this->max;
    }

    /**
     * @param Position $position
     * @return bool
     */
    public function inZone(Position $position): bool
    {
        if($position->getWorld() === $this->world){
            if($position->x >= $this->min->x and $position->x <= $this->max->x){
                if($position->y >= $this->min->y and $position->y <= $this->max->y){
                    if($position->z >= $this->min->z and $position->z <= $this->max->z){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return int[]
     */
    public function getAllowedBlocks(): array
    {
        return $this->allowed;
    }

    /**
     * @return array
     */
    public function getNewBlocks(): array
    {
        return $this->newblock;
    }

    /**
     * @return int
     */
    public function getTimer(): int
    {
        return $this->timer;
    }
}