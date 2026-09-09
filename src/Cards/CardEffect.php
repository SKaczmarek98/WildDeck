<?php

declare(strict_types=1);

namespace WildDeck\Cards;

use WildDeck\Effect\EffectInterface;
use WildDeck\Target\TargetResolverInterface;

final readonly class CardEffect
{
    public function __construct(
        public EffectInterface         $effect,
        public TargetResolverInterface $target,
    )
    {
    }
}