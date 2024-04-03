<?php

namespace Valres\MineSystem\trait;

use Valres\MineSystem\zones\CacheManager;
use Valres\MineSystem\zones\ZoneManager;

trait LoaderTrait
{
    public ZoneManager $zoneManager;
    public CacheManager $cacheManager;
    final public function loadAll(): void
    {
        $this->initAll();
    }

    final public function initAll(): void
    {
        $this->zoneManager = new ZoneManager();
        $this->cacheManager = new CacheManager();
    }
}
