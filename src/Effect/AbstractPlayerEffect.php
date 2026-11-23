<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combat\CombatInterface;
use WildDeck\Combatant\Player;

abstract class AbstractPlayerEffect implements EffectInterface
{
    /**
     * @param Player[] $targets
     */
    final public function apply(CombatInterface $combat, array $targets): void
    {
        foreach ($targets as $target) {
            $this->applyToTarget($combat, $target);
        }
    }

    abstract protected function applyToTarget(
        CombatInterface $combat,
        Player          $target
    ): void;

}
