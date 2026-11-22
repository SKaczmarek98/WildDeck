<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\Player;
use WildDeck\Game\Game;

final class DrawEffect implements EffectInterface
{
    public function __construct(
        private int $amount,
    )
    {
    }

    /**
     * @param Player[] $targets
     */
    final public function apply(Game $game, array $targets): void
    {
        foreach ($targets as $target) {
            $this->applyToTarget($game, $target);
        }
    }

    private function applyToTarget(
        Game   $game,
        Player $target
    ): void
    {
        for ($i = 0; $i < $this->amount; $i++) {
            $game->drawCardFor($target);
        }
    }
}
