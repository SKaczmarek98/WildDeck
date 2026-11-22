<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\Combatant;
use WildDeck\Game\Game;

abstract class AbstractEffect implements EffectInterface
{
    /**
     * @param Combatant[] $targets
     */
    final public function apply(Game $game, array $targets): void
    {
        foreach ($targets as $target) {
            $this->applyToTarget($game, $target);
        }
    }

    abstract protected function applyToTarget(
        Game      $game,
        Combatant $target
    ): void;

}
