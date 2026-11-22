<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\Combatant;
use WildDeck\Game\Game;

final class HealEffect extends AbstractEffect
{
    public function __construct(
        private int $health,
    )
    {
    }

    protected function applyToTarget(Game $game, Combatant $target): void
    {
        $target->heal($this->health);
    }

}
