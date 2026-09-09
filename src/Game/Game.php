<?php

declare(strict_types=1);

namespace WildDeck\Game;

use WildDeck\Cards\Card;
use WildDeck\Cards\CardCollection;
use WildDeck\Cards\Deck;
use WildDeck\Game\Exception\CardNotInHandException;
use WildDeck\Game\Exception\GameAlreadyEndedException;
use WildDeck\Game\Exception\NoAlivePlayerFoundException;
use WildDeck\Game\Exception\NotEnoughCardsToStartGameException;
use WildDeck\Game\Exception\NotPlayerTurnException;
use WildDeck\Game\Exception\PlayerNotInGameException;

class Game
{
    private const int CARD_PER_PLAYER = 2;

    /**
     * @var Player[]
     */
    private array $players;
    private int $currentPlayerIndex = 0;
    private ?Player $winner = null;

    private bool $gameIsEnd;
    private CardCollection $drawPile;
    private CardCollection $discardPile;


    /**
     * @throws NotEnoughCardsToStartGameException
     * @var Player[]
     */
    public function __construct(
        array $players,
        Deck  $deck
    )
    {
        $this->drawPile = new CardCollection();
        $this->discardPile = new CardCollection();
        $this->players = $players;
        $this->currentPlayerIndex = 0;
        $this->gameIsEnd = false;

        $this->preapareCards($deck);
    }

    /**
     * @throws NotEnoughCardsToStartGameException
     */
    private function preapareCards(
        Deck $deck
    ): void
    {
        $requiredCards = self::CARD_PER_PLAYER * count($this->players);

        if ($deck->count() < $requiredCards) {
            throw new NotEnoughCardsToStartGameException();
        }

        foreach ($deck->getAll() as $card) {
            $this->drawPile->add($card);
        }

        for ($i = 0; $i < self::CARD_PER_PLAYER; $i++) {
            foreach ($this->players as $player) {
                $player->getHand()->add(
                    $this->drawPile->takeFirst()
                );
            }
        }

    }

    public function playCard(
        Player  $player,
        Card    $card,
        ?Player $target = null
    ): void
    {
        $this->validatePlayCard($player, $card);

        foreach ($card->getEffects() as $effect) {
            $effect->effect->apply($this, $effect->target->resolve($this, $player, $target));
        }

        $this->checkGameEnd();

        $this->discardPile->add($player->getHand()->take($card));
    }

    private function validatePlayCard(
        Player $player,
        Card   $card
    ): void
    {
        if ($this->gameIsEnd) {
            throw new GameAlreadyEndedException();
        }

        if ($this->getCurrentPlayer() !== $player) {
            throw new NotPlayerTurnException();
        }

        if (!$player->getHand()->contains($card)) {
            throw new CardNotInHandException();
        }

    }

    private function checkGameEnd(): void
    {
        $alivePlayers = array_values(
            array_filter(
                $this->players,
                static fn(Player $player): bool => $player->isAlive(),
            )
        );

        if (count($alivePlayers) > 1) {
            return;
        }

        $this->gameIsEnd = true;
        $this->winner = $alivePlayers[0] ?? null;
    }

    public function endTurn(
        Player $player
    ): void
    {
        if ($this->gameIsEnd) {
            throw new GameAlreadyEndedException();
        }

        if ($this->getCurrentPlayer() !== $player) {
            throw new NotPlayerTurnException();
        }

        $playersCount = count($this->players);

        for ($i = 0; $i < $playersCount; $i++) {
            $this->currentPlayerIndex = ($this->currentPlayerIndex + 1) % $playersCount;

            if ($this->getCurrentPlayer()->isAlive()) {
                return;
            }
        }

        throw new NoAlivePlayerFoundException();
    }

    private function getCurrentPlayer(): Player
    {
        return $this->players[$this->currentPlayerIndex];
    }

    public function isEnded(): bool
    {
        return $this->gameIsEnd;
    }

    public function getWinner(): ?Player
    {
        return $this->winner;
    }

    public function drawCardFor(
        Player $player
    ): void
    {
        $card = $this->drawPile->takeFirst();

        if ($card === null) {
            return;
        }

        $player->getHand()->add($card);
    }

    /**
     * @return Player[]
     */
    public function getOpponentsOf(Player $player): array
    {
        $this->validatePlayerInGame($player);

        return array_values(
            array_filter(
                $this->players,
                static fn(Player $candidate): bool => $candidate !== $player,
            ),
        );
    }

    private function validatePlayerInGame(Player $player): void
    {
        if (!in_array($player, $this->players, true)) {
            throw new PlayerNotInGameException();
        }
    }
}
