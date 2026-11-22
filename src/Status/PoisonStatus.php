<?php

declare(strict_types=1);

namespace WildDeck\Status;

use WildDeck\Combatant\CombatantInterface;

final class PoisonStatus extends AbstractStatus
{
    public function __construct(
        int $duration,
    ) {
        parent::__construct($duration);
    }

    protected function applyEffect(CombatantInterface $player): void
    {
        $player->takeDamage($this->remainingTurns);
    }
}
