<?php

declare(strict_types=1);

namespace Valres\MineSystem\utils;

final class WeightedBlockPicker {
    public function pick(array $weights): ?string {
        $validWeights = [];
        $total = 0;

        foreach ($weights as $blockName => $weight) {
            $weight = (int) $weight;
            if ($weight <= 0) {
                continue;
            }

            $validWeights[(string) $blockName] = $weight;
            $total += $weight;
        }

        if ($total <= 0) {
            return null;
        }

        $rand = mt_rand(1, $total);

        foreach ($validWeights as $blockName => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $blockName;
            }
        }

        return null;
    }
}
