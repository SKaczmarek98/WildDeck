<?php

declare(strict_types=1);

namespace WildDeck\Cards;

use WildDeck\Requirement\RequirementInterface;

final class Card
{
    /**
     * @param CardEffect[] $effects
     * @param RequirementInterface[] $requirements
     */
    public function __construct(
        private string $name,
        private array  $effects,
        private int    $cost = 0,
        private array  $requirements = [],
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

    public function getCost(): int
    {
        return $this->cost;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

}
