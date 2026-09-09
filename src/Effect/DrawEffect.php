<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;

final class DrawEffect implements EffectInterface
{

    public function __construct(
        private int $amount,
    )
    {
    }

    public function apply(Game $game, array $targets): void
    {
        foreach ($targets as $target) {
            for ($i = 0; $i < $this->amount; $i++) {
                $game->drawCardFor($target);
            }
        }
    }
}
