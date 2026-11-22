<?php

declare(strict_types=1);

namespace WildDeck\Game;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Effect\EffectInterface;

final readonly class ResolvedEffect
{
    /**
     * @param EffectInterface $effect
     * @param CombatantInterface[] $targets
     */
    public function __construct(
        public EffectInterface $effect,
        public array           $targets = []
    ) {
    }
}
