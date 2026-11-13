<?php

namespace WildDeck\Status;

use WildDeck\Game\Player;

interface StatusInterface
{
    public function onTurnStart(Player $player): void;

    public function isExpired(): bool;
}
