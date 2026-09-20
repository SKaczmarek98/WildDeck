<?php

declare(strict_types=1);

namespace WildDeck\Game;

use InvalidArgumentException;
use WildDeck\Cards\CardCollection;
use WildDeck\Game\Exception\NoManaToPlayCardException;

class Player
{
    private const int MAX_LIFE_POINTS = 20;
    private const int MIN_LIFE_POINTS = 0;
    private const int MAX_MANA_POINTS = 3;
    private const int MIN_MANA_POINTS = 0;

    private string $name;

    private CardCollection $hand;

    private int $lifePoints;
    private int $manaPoints;


    public function __construct(string $name)
    {
        $this->name = $name;
        $this->lifePoints = self::MAX_LIFE_POINTS;
        $this->manaPoints = self::MAX_MANA_POINTS;
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

    public function getManaPoints(): int
    {
        return $this->manaPoints;
    }

    public function takeMana(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException();
        }

        if ($amount > $this->manaPoints) {
            throw new NoManaToPlayCardException();
        }

        $this->manaPoints = max(
            self::MIN_MANA_POINTS,
            $this->manaPoints - $amount,
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
