<?php

declare(strict_types=1);

namespace WildDeck\Status;

use WildDeck\Game\Player;

final class PoisionStatus extends AbstractStatus
{
    public function __construct(
        private int $damage,
        int         $duration,
    )
    {
        parent::__construct($duration);
    }

    protected function applyEffect(Player $player): void
    {
        $player->takeDamage($this->damage);
    }
}
