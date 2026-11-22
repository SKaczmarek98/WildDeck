<?php

namespace WildDeck\Status;

use WildDeck\Combatant\Combatant;

interface StatusInterface
{
    public function onTurnStart(Combatant $player): void;

    public function isExpired(): bool;

    public function tryStack(StatusInterface $status): bool;

}
