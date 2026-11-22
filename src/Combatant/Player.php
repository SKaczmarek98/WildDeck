<?php

declare(strict_types=1);

namespace WildDeck\Combatant;

use InvalidArgumentException;
use WildDeck\Cards\CardCollection;
use WildDeck\Game\Exception\NoManaToPlayCardException;

class Player extends AbstractCombatant
{
    private const int MAX_LIFE_POINTS = 20;
    private const int MAX_MANA_POINTS = 3;
    private const int MIN_MANA_POINTS = 0;


    private CardCollection $hand;
    private int $manaPoints;


    public function __construct(string $name)
    {
        $this->manaPoints = self::MAX_MANA_POINTS;
        $this->hand = new CardCollection();

        parent::__construct($name, self::MAX_LIFE_POINTS);
    }

    public function getHand(): CardCollection
    {
        return $this->hand;
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

    public function restoreManaToMax(): void
    {
        $this->restoreMana(self::MAX_MANA_POINTS);
    }

    public function restoreMana(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException();
        }

        $this->manaPoints = min(
            self::MAX_MANA_POINTS,
            $this->manaPoints + $amount,
        );
    }

}
