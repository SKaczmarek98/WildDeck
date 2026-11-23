<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combat\CombatInterface;
use WildDeck\Combatant\Player;

final class DrawEffect extends AbstractPlayerEffect
{
    public function __construct(
        private int $amount,
    ) {
    }

    protected function applyToTarget(
        CombatInterface $combat,
        Player          $target
    ): void {
        for ($i = 0; $i < $this->amount; $i++) {
            $combat->drawCardFor($target);
        }
    }
}
