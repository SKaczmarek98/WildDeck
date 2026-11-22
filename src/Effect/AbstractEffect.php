<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combat\CombatInterface;

abstract class AbstractEffect implements EffectInterface
{
    /**
     * @param CombatantInterface[] $targets
     */
    final public function apply(CombatInterface $combat, array $targets): void
    {
        foreach ($targets as $target) {
            $this->applyToTarget($combat, $target);
        }
    }

    abstract protected function applyToTarget(
        CombatInterface    $combat,
        CombatantInterface $target
    ): void;

}
