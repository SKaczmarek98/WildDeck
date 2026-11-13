<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Status\StatusInterface;
use WildDeck\Game\Player;

final class ApplyStatusEffect extends AbstractEffect
{
    public function __construct(
        private StatusInterface $status,
    ) {}

    protected function applyToTarget(
        Game $game,
        Player $target
    ): void {
        $target->addStatus(clone $this->status);
    }
}
