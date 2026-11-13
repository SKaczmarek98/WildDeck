<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

final class HealEffect extends AbstractEffect
{
    public function __construct(
        private int $health,
    )
    {
    }

    protected function applyToTarget(Game $game, Player $target): void
    {
        $target->heal($this->health);
    }

}
