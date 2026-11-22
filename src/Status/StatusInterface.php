<?php

namespace WildDeck\Status;

use WildDeck\Combatant\CombatantInterface;

interface StatusInterface
{
    public function onTurnStart(CombatantInterface $combatant): void;

    public function isExpired(): bool;

    public function tryStack(StatusInterface $status): bool;

}
