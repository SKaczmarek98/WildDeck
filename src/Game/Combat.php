<?php

declare(strict_types=1);

namespace WildDeck\Game;

use WildDeck\Cards\Card;
use WildDeck\Cards\CardCollection;
use WildDeck\Cards\Deck;
use WildDeck\Combatant\Combatant;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;
use WildDeck\Game\Exception\CardNotInHandException;
use WildDeck\Game\Exception\GameAlreadyEndedException;
use WildDeck\Game\Exception\NoAlivePlayerFoundException;
use WildDeck\Game\Exception\NoManaToPlayCardException;
use WildDeck\Game\Exception\NotEnoughCardsToStartGameException;
use WildDeck\Game\Exception\NotPlayerTurnException;
use WildDeck\Game\Exception\PlayerNotInGameException;
use WildDeck\Game\Exception\RequirementNotSatisfiedException;

class Combat
{
    private const int CARD_PER_PLAYER = 2;

    /**
     * @var Enemy[]
     */
    private array $enemies;
    private Player $player;

    /**
     * @var Combatant[]
     */
    private array $combatants;
    private int $currentCombatantIndex = 0;

    private bool $gameIsEnd;
    private CardCollection $drawPile;
    private CardCollection $discardPile;
    private ?CombatResult $outcome = null;


    /**
     * @throws NotEnoughCardsToStartGameException
     * @var Player[]
     */
    public function __construct(
        Player $player,
        array  $enemies,
        Deck   $deck
    ) {
        $this->drawPile = new CardCollection();
        $this->discardPile = new CardCollection();
        $this->player = $player;
        $this->enemies = $enemies;
        $this->combatants = [$player] + $enemies;
        $this->currentCombatantIndex = 0;
        $this->gameIsEnd = false;

        $this->preapareCards($deck);
        $this->startTurn();
    }

    /**
     * @throws NotEnoughCardsToStartGameException
     */
    private function preapareCards(
        Deck $deck
    ): void {
        if ($deck->count() < self::CARD_PER_PLAYER) {
            throw new NotEnoughCardsToStartGameException();
        }

        $deck->shuffle();

        foreach ($deck->getAll() as $card) {
            $this->drawPile->add($card);
        }

        for ($i = 0; $i < self::CARD_PER_PLAYER; $i++) {
            $this->player->getHand()->add(
                $this->drawPile->takeFirst()
            );
        }
    }

    private function startTurn(): void
    {
        while (true) {
            $combatant = $this->getCurrentCombatant();
            $combatant->processStatues();

            if ($combatant instanceof Player) {
                $this->drawCardFor($combatant);
                $combatant->restoreManaToMax();
            }

            $this->checkGameEnd();

            if ($this->gameIsEnd) {
                return;
            }

            if ($combatant->isAlive()) {
                return;
            }

            $this->moveToNextAlivePlayer();
        }
    }

    private function getCurrentCombatant(): Combatant
    {
        return $this->combatants[$this->currentCombatantIndex];
    }

    public function drawCardFor(
        Player $player
    ): void {
        $card = $this->drawPile->takeFirst();

        if ($card === null) {
            return;
        }

        $player->getHand()->add($card);
    }

    private function checkGameEnd(): void
    {
        if (!$this->player->isAlive()) {
            $this->outcome = CombatResult::ENEMY_WIN;

            return;
        }

        foreach ($this->enemies as $enemy) {
            if ($enemy->isAlive()) {
                return;
            }
        }

        $this->outcome = CombatResult::PLAYER_WIN;
    }


    private function moveToNextAlivePlayer(): void
    {
        $combatantsCount = count($this->combatants);

        for ($i = 0; $i < $combatantsCount; $i++) {
            $this->currentCombatantIndex
                = ($this->currentCombatantIndex + 1) % $combatantsCount;

            if ($this->getCurrentCombatant()->isAlive()) {
                return;
            }
        }

        throw new NoAlivePlayerFoundException();
    }

    public function playCard(
        Player  $player,
        Card    $card,
        ?Player $target = null
    ): void {
        $this->validatePlayCard($player, $card);

        /**
         * @var PreparedEffect[] $preparedEffects
         */
        $preparedEffects = [];

        foreach ($card->getEffects() as $effect) {
            $preparedEffects[] = new PreparedEffect(
                $effect->effect,
                $effect->target->resolve($this, $player, $target)
            );
        }

        $player->takeMana($card->getCost());

        foreach ($preparedEffects as $resolvedEffect) {
            $resolvedEffect->effect->apply($this, $resolvedEffect->targets);
        }

        $this->checkGameEnd();

        $this->discardPile->add($player->getHand()->take($card));
    }

    private function validatePlayCard(
        Player $player,
        Card   $card
    ): void {
        if ($this->gameIsEnd) {
            throw new GameAlreadyEndedException();
        }

        if ($this->getCurrentCombatant() !== $player) {
            throw new NotPlayerTurnException();
        }

        if (!$player->getHand()->contains($card)) {
            throw new CardNotInHandException();
        }

        if ($card->getCost() > $player->getManaPoints()) {
            throw new NoManaToPlayCardException();
        }

        $this->canPlayCard($player, $card);
    }

    private function canPlayCard(
        Player $player,
        Card   $card,
    ): void {
        foreach ($card->getRequirements() as $requirement) {
            $result = $requirement->check($this, $player);

            if (!$result->satisfied) {
                throw new RequirementNotSatisfiedException($result->reason ?? 'CARD_REQUIREMENT_NOT_SATISFIED');
            }
        }
    }

    public function endTurn(
        Player $player
    ): void {
        if ($this->gameIsEnd) {
            throw new GameAlreadyEndedException();
        }

        if ($this->getCurrentCombatant() !== $player) {
            throw new NotPlayerTurnException();
        }

        $this->moveToNextAlivePlayer();
        $this->startTurn();
    }

    public function isEnded(): bool
    {
        return $this->gameIsEnd;
    }

    public function getWinner(): ?CombatResult
    {
        return $this->outcome;
    }

    /**
     * @return Player[]
     */
    public function getOpponentsOf(
        Player $player
    ): array {
        $this->validatePlayerInGame($player);

        return array_values(
            array_filter(
                $this->enemies,
                static fn(Player $candidate): bool => $candidate !== $player,
            ),
        );
    }

    private function validatePlayerInGame(
        Player $player
    ): void {
        if (!in_array($player, $this->enemies, true)) {
            throw new PlayerNotInGameException();
        }
    }
}
