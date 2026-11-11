<?php

declare(strict_types=1);

namespace WildDeck\Tests;

use WildDeck\Cards\Card;
use WildDeck\Cards\CardEffect;
use WildDeck\Cards\Deck;
use WildDeck\Effect\DamageEffect;
use WildDeck\Effect\HealEffect;
use WildDeck\Target\SelectedOpponentTarget;
use WildDeck\Target\SelfTargetResolver;

class TestHelper
{

    public static function createDeck(
        ?Card $aliceCard = null,
        ?Card $bobCard = null,
    ): Deck
    {
        return new Deck([
                            $aliceCard ?? new Card('Alice filler 1', []),
                            $bobCard ?? new Card('Bob filler 1', []),
                            new Card('Alice filler 2', []),
                            new Card('Bob filler 2', []),
                        ]);
    }

    public static function createFireballCard(int $amount = 5, int $cost = 0): Card
    {
        return new Card('Fireball', [
            new CardEffect(
                new DamageEffect($amount),
                new SelectedOpponentTarget()
            )
        ],
                        $cost
        );
    }

    public static function createHealCard(int $amount = 5): Card
    {
        return new Card('Heal', [
            new CardEffect(
                new HealEffect($amount),
                new SelfTargetResolver()
            )
        ]);
    }

}