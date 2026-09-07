<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

final class DamageEffect implements EffectInterface
{
    public function __construct(
        private int $amount,
    )
    {
    }

    public function apply(Game $game, Player $source): void
    {
        $opponent = $game->getOpponentOf($source);

        $opponent->takeDamage($this->amount);
    }
}