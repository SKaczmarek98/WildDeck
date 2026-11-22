<?php

namespace WildDeck\Status;

use WildDeck\Combatant\AbstractCombatant;

interface StatusInterface
{
    public function onTurnStart(AbstractCombatant $player): void;

    public function isExpired(): bool;

    public function tryStack(StatusInterface $status): bool;

}
