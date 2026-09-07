<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

interface EffectInterface
{
    public function apply(Game $game, Player $source): void;

}