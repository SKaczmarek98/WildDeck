<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combat\CombatInterface;
use WildDeck\Status\StatusInterface;

final class ApplyStatusEffect extends AbstractEffect
{
    public function __construct(
        private StatusInterface $status,
    ) {
    }

    protected function applyToTarget(
        CombatInterface    $combat,
        CombatantInterface $target
    ): void {
        $target->addStatus(clone $this->status);
    }
}
