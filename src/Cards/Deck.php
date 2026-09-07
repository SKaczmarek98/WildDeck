<?php

declare(strict_types=1);

namespace WildDeck\Cards;

use WildDeck\Effect\DamageEffect;
use WildDeck\Effect\DrawEffect;
use WildDeck\Effect\HealEffect;

final class Deck extends CardCollection
{
    public static function demo(): self
    {
        return new self([
                            Deck::newFireballCard(),
                            Deck::newFireballCard(),
                            Deck::newFireballCard(),
                            Deck::newFireballCard(),
                            Deck::newHealCard(),
                            Deck::newHealCard(),
                            Deck::newDrawCard(),
                        ]);
    }

    private static function newFireballCard(): Card
    {
        return new Card(
            'Fireball',
            [new DamageEffect(5)],
        );
    }

    private static function newHealCard(): Card
    {
        return new Card(
            'Heal',
            [new HealEffect(5)],
        );
    }

    private static function newDrawCard(): Card
    {
        return new Card(
            'Draw',
            [new DrawEffect(5)],
        );
    }

}
