<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;

final class HealEffect implements EffectInterface
{
    public function __construct(
        private int $health,
    )
    {
    }

    public function apply(Game $game, array $targets): void
    {
        foreach ($targets as $target) {
            $target->heal($this->health);
        }
    }
}
