<?php

namespace WildDeck\Target;

use WildDeck\Combatant\Combatant;
use WildDeck\Combatant\Player;
use WildDeck\Game\Game;

interface TargetResolverInterface
{

    /**
     * @return Player[]
     */
    public function resolve(
        Game       $game,
        Combatant  $source,
        ?Combatant $selectedTarget,
    ): array;
}
