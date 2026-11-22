<?php

declare(strict_types=1);

namespace WildDeck\Cards;

final class Deck extends CardCollection
{
    public function shuffle(): void
    {
        shuffle($this->cards);
    }
}
