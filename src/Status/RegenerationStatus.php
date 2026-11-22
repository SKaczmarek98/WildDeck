<?php

declare(strict_types=1);

namespace WildDeck\Status;

use WildDeck\Combatant\CombatantInterface;

final class RegenerationStatus extends AbstractStatus
{
    public function __construct(
        private int $heal,
        int         $duration,
    ) {
        parent::__construct($duration);
    }

    protected function applyEffect(CombatantInterface $player): void
    {
        $player->heal($this->heal);
    }
}