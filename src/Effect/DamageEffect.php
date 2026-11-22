<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combat\CombatInterface;

final class DamageEffect extends AbstractEffect
{
    public function __construct(
        private int $amount,
    ) {
    }

    protected function applyToTarget(
        CombatInterface    $combat,
        CombatantInterface $target
    ): void {
        $target->takeDamage($this->amount);
    }

}
