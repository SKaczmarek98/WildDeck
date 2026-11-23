<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combat\CombatInterface;
use WildDeck\Combatant\Player;

class RestoreManaEffect extends AbstractPlayerEffect
{
    public function __construct(
        private int $amount,
    ) {
    }

    protected function applyToTarget(
        CombatInterface $combat,
        Player          $target
    ): void {
        $target->restoreMana($this->amount);
    }

}
