<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Game\CombatInterface;

final class HealEffect extends AbstractEffect
{
    public function __construct(
        private int $health,
    ) {
    }

    protected function applyToTarget(CombatInterface $combat, CombatantInterface $target): void
    {
        $target->heal($this->health);
    }

}
