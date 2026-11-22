<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\Combatant;
use WildDeck\Game\Game;
use WildDeck\Status\StatusInterface;

final class ApplyStatusEffect extends AbstractEffect
{
    public function __construct(
        private StatusInterface $status,
    )
    {
    }

    protected function applyToTarget(
        Game      $game,
        Combatant $target
    ): void
    {
        $target->addStatus(clone $this->status);
    }
}
