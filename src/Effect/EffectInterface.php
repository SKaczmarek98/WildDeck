<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\Combatant;
use WildDeck\Game\Game;

interface EffectInterface
{

    /**
     * @param Combatant[] $targets
     */
    public function apply(Game $game, array $targets): void;

}
