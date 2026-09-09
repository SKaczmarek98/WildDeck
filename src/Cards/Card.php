<?php

declare(strict_types=1);

namespace WildDeck\Cards;

final class Card
{
    /**
     * @param CardEffect[] $effects
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
     * @return CardEffect[]
     */
    public function getEffects(): array
    {
        return $this->effects;
    }

}
