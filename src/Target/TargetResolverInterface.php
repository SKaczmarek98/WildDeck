<?php

namespace WildDeck\Target;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

interface TargetResolverInterface
{

    /**
     * @return Player[]
     */
    public function resolve(
        Game    $game,
        Player  $source,
        ?Player $selectedTarget,
    ): array;
}
