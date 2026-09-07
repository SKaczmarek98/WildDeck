<?php

declare(strict_types=1);

namespace WildDeck\Game;

use WildDeck\Cards\Card;
use WildDeck\Cards\CardCollection;
use WildDeck\Cards\Deck;
use WildDeck\Game\Exception\CardNotInHandException;
use WildDeck\Game\Exception\GameAlreadyEndedException;
use WildDeck\Game\Exception\NotEnoughCardsToStartGameException;
use WildDeck\Game\Exception\NotPlayerTurnException;
use WildDeck\Game\Exception\PlayerNotInGameException;

class Game
{
    private const int CARD_PER_PLAYER = 2;

    private Player $playerOne;
    private Player $playerTwo;
    private Player $currentPlayer;
    private ?Player $winner = null;

    private bool $gameIsEnd;
    private CardCollection $drawPile;
    private CardCollection $discardPile;


    public function __construct(
        Player $playerOne,
        Player $playerTwo,
        Deck   $deck
    )
    {
        $this->drawPile = new CardCollection();
        $this->discardPile = new CardCollection();
        $this->createGame($playerOne, $playerTwo, $deck);
    }


    private function createGame(Player $playerOne, Player $playerTwo, Deck $deck): void
    {
        $requiredCards = self::CARD_PER_PLAYER * 2;

        if ($deck->count() < $requiredCards) {
            throw new NotEnoughCardsToStartGameException();
        }

        foreach ($deck->getAll() as $card) {
            $this->drawPile->add($card);
        }

        $this->playerOne = $playerOne;
        $this->playerTwo = $playerTwo;
        $this->currentPlayer = $playerOne;
        $this->gameIsEnd = false;


        $players = [
            $this->playerOne,
            $this->playerTwo,
        ];

        for ($i = 0; $i < self::CARD_PER_PLAYER; $i++) {
            foreach ($players as $player) {
                $player->getHand()->add(
                    $this->drawPile->takeFirst()
                );
            }
        }

    }

    public function playCard(Player $player, Card $card): void
    {
        $this->validatePlayCard($player, $card);

        $opponent = $this->getOpponentOf($player);

        foreach ($card->getEffects() as $effect) {
            $effect->apply($this, $player);
        }

        if ($opponent->getLifePoints() <= 0) {
            $this->endGame($player);
        }

        $this->discardPile->add($player->getHand()->take($card));
    }

    private function validatePlayCard(Player $player, Card $card): void
    {
        if ($this->gameIsEnd) {
            throw new GameAlreadyEndedException();
        }

        if ($this->currentPlayer !== $player) {
            throw new NotPlayerTurnException();
        }

        if (!$player->getHand()->contains($card)) {
            throw new CardNotInHandException();
        }

    }

    public function endTurn(Player $player): void
    {
        if ($this->gameIsEnd) {
            throw new GameAlreadyEndedException();
        }

        if ($this->currentPlayer !== $player) {
            throw new NotPlayerTurnException();
        }

        $this->currentPlayer = $this->getOpponentOf($player);
    }

    private function endGame(Player $player): void
    {
        $this->gameIsEnd = true;
        $this->winner = $player;
    }

    public function isEnded(): bool
    {
        return $this->gameIsEnd;
    }

    public function getWinner(): ?Player
    {
        return $this->winner;
    }

    public function getOpponentOf(Player $player): Player
    {
        if ($player === $this->playerOne) {
            return $this->playerTwo;
        }

        if ($player === $this->playerTwo) {
            return $this->playerOne;
        }

        throw new PlayerNotInGameException();
    }

    public function drawCardFor(Player $player): void
    {
        $card = $this->drawPile->takeFirst();

        if ($card === null) {
            return;
        }

        $player->getHand()->add($card);
    }
}
