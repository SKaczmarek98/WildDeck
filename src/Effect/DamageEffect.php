<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

final class DamageEffect implements EffectInterface
{
    public function __construct(
        private int $amount,
    )
    {
    }

    /**
     * @param Player[] $targets
     */
    public function apply(Game $game, array $targets): void
    {
        foreach ($targets as $target) {
            $target->takeDamage($this->amount);
        }
    }
}
