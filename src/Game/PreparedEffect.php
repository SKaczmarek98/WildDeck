<?php

declare(strict_types=1);

namespace WildDeck\Game;

use WildDeck\Effect\EffectInterface;

final readonly class PreparedEffect
{
    /**
     * @param EffectInterface $effect
     * @param Player[] $targets
     */
    public function __construct(
        public EffectInterface $effect,
        public array $targets = []
    )
    {
    }
}