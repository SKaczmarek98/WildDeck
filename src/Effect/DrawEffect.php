<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

final class DrawEffect extends AbstractEffect
{
    public function __construct(
        private int $amount,
    ) {}

    protected function applyToTarget(
        Game $game,
        Player $target
    ): void {
        for ($i = 0; $i < $this->amount; $i++) {
            $game->drawCardFor($target);
        }
    }

}
