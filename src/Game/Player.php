<?php

declare(strict_types=1);

namespace WildDeck\Game;

use InvalidArgumentException;
use WildDeck\Cards\CardCollection;

class Player
{
    private const int MAX_LIFE_POINTS = 20;
    private const int MIN_LIFE_POINTS = 0;
    private string $name;

    private CardCollection $hand;

    private int $lifePoints;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->lifePoints = 20;
        $this->hand = new CardCollection();
    }

    public function getHand(): CardCollection
    {
        return $this->hand;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLifePoints(): int
    {
        return $this->lifePoints;
    }

    public function takeDamage(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException();
        }

        $this->lifePoints = max(
            self::MIN_LIFE_POINTS,
            $this->lifePoints - $amount,
        );
    }

    public function heal(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException();
        }

        $this->lifePoints = min(
            self::MAX_LIFE_POINTS,
            $this->lifePoints + $amount,
        );
    }

    public function isAlive(): bool
    {
        return $this->lifePoints > 0;
    }

}
