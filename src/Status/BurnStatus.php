<?php

declare(strict_types=1);

namespace WildDeck\Status;

use WildDeck\Combatant\CombatantInterface;

final class BurnStatus extends AbstractStatus
{
    public function __construct(
        private int $damage,
        int         $duration,
    ) {
        parent::__construct($duration);
    }

    protected function applyEffect(CombatantInterface $combatant): void
    {
        $combatant->takeDamage($this->damage);
    }

}
