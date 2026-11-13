<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

abstract class AbstractEffect implements EffectInterface
{
    /**
     * @param Player[] $targets
     */
    final public function apply(Game $game, array $targets): void
    {
        foreach ($targets as $target) {
            $this->applyToTarget($game, $target);
        }
    }

    abstract protected function applyToTarget(
        Game $game,
        Player $target
    ): void;

}
