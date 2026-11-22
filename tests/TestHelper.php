<?php

declare(strict_types=1);

namespace WildDeck\Tests;

use WildDeck\Cards\Card;
use WildDeck\Cards\CardEffect;
use WildDeck\Cards\Deck;
use WildDeck\Effect\DamageEffect;
use WildDeck\Target\SelectedOpponentTarget;

final class TestHelper
{
    // Every card in this deck fits in the initial hand, regardless of shuffle.
    public static function createDeck(?Card $card = null): Deck
    {
        return new Deck([
            $card ?? new Card('Filler 1', []),
            new Card('Filler 2', []),
        ]);
    }

    public static function createFireballCard(int $amount = 5, int $cost = 0): Card
    {
        return new Card('Fireball', [
            new CardEffect(new DamageEffect($amount), new SelectedOpponentTarget()),
        ], $cost);
    }
}
