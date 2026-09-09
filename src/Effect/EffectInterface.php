<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

interface EffectInterface
{

    /**
     * @param Player[] $targets
     */
    public function apply(Game $game, array $targets): void;

}
