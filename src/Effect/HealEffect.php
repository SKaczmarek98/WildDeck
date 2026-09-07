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

    public function apply(Game $game, Player $source): void
    {
        $source->heal($this->health);
    }
}