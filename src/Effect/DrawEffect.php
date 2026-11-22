<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\Player;
use WildDeck\Combat\CombatInterface;

final class DrawEffect implements EffectInterface
{
    public function __construct(
        private int $amount,
    ) {
    }

    /**
     * @param Player[] $targets
     */
    final public function apply(CombatInterface $combat, array $targets): void
    {
        foreach ($targets as $target) {
            $this->applyToTarget($combat, $target);
        }
    }

    private function applyToTarget(
        CombatInterface $combat,
        Player          $target
    ): void {
        for ($i = 0; $i < $this->amount; $i++) {
            $combat->drawCardFor($target);
        }
    }
}
