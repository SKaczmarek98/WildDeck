<?php

declare(strict_types=1);

namespace WildDeck\Cards;

use WildDeck\Cards\Exception\CardNotInCollectionException;

class CardCollection
{
    /**
     * @param Card[] $cards
     */
    public function __construct(
        private array $cards = [],
    )
    {
    }

    public function add(Card $card): void
    {
        $this->cards[] = $card;
    }

    public function contains(Card $card): bool
    {
        return in_array($card, $this->cards, true);
    }

    public function count(): int
    {
        return count($this->cards);
    }

    public function getAll(): array
    {
        return $this->cards;
    }

    public function takeFirst(): ?Card
    {
        $key = array_key_first($this->cards);

        if ($key === null) {
            return null;
        }

        $card = $this->cards[$key];
        unset($this->cards[$key]);

        return $card;
    }

    public function take(Card $card): Card
    {
        $key = array_search($card, $this->cards, true);

        if ($key === false) {
            throw new CardNotInCollectionException();
        }

        unset($this->cards[$key]);

        return $card;
    }

}
