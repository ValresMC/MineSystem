<?php

declare(strict_types=1);

namespace Valres\MineSystem\zones;

use pocketmine\math\AxisAlignedBB;
use pocketmine\world\Position;
use pocketmine\world\World;

final readonly class Zone {
    public function __construct(
        private string $name,
        private World $world,
        private AxisAlignedBB $zone,
        private array $allowed,
        private array $newBlock,
        private int $timer
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getWorld(): World {
        return $this->world;
    }

    public function getZone(): AxisAlignedBB {
        return $this->zone;
    }

    public function inZone(Position $position): bool {
        return $this->zone->isVectorInside($position);
    }

    public function getAllowedBlocks(): array {
        return $this->allowed;
    }

    public function getNewBlocks(): array {
        return $this->newBlock;
    }

    public function getTimer(): int {
        return $this->timer;
    }
}
