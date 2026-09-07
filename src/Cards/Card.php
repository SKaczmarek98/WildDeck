<?php

declare(strict_types=1);

namespace WildDeck\Cards;

use WildDeck\Effect\EffectInterface;

final class Card
{
    /**
     * @param EffectInterface[] $effects
     */
    public function __construct(
        private string $name,
        private array  $effects,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return EffectInterface[]
     */
    public function getEffects(): array
    {
        return $this->effects;
    }
}