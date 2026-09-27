<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

final class HealEffect implements EffectInterface
{
    public function __construct(
        private int $health,
    )
    {
    }

    /**
     * @param Player[] $targets
     */
    public function apply(Game $game, array $targets): void
    {
        foreach ($targets as $target) {
            $target->heal($this->health);
        }
    }
}
