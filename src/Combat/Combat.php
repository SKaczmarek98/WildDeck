<?php

declare(strict_types=1);

namespace WildDeck\Combat;

use WildDeck\Cards\Card;
use WildDeck\Cards\CardCollection;
use WildDeck\Cards\Deck;
use WildDeck\Combat\Exception\CardNotInHandException;
use WildDeck\Combat\Exception\CombatantNotInCombatException;
use WildDeck\Combat\Exception\CombatAlreadyEndedException;
use WildDeck\Combat\Exception\NoAliveCombatantFoundException;
use WildDeck\Combat\Exception\NoManaToPlayCardException;
use WildDeck\Combat\Exception\NotEnoughCardsToStartCombatException;
use WildDeck\Combat\Exception\NotPlayerTurnException;
use WildDeck\Combat\Exception\RequirementNotSatisfiedException;
use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;

class Combat implements CombatInterface
{
    private const int STARTING_HAND_SIZE = 2;

    /**
     * @var Enemy[]
     */
    private array $enemies;
    private Player $player;

    /**
     * @var CombatantInterface[]
     */
    private array $combatants;
    private int $currentCombatantIndex = 0;
    private CardCollection $drawPile;
    private CardCollection $discardPile;
    private ?CombatOutcome $outcome = null;


    /**
     * @throws NotEnoughCardsToStartCombatException
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
        $this->combatants = array_merge(
            [$player],
            $enemies,
        );
        $this->currentCombatantIndex = 0;

        $this->prepareCards($deck);
        $this->startCurrentTurn();
    }

    /**
     * @throws NotEnoughCardsToStartCombatException
     */
    private function prepareCards(
        Deck $deck
    ): void {
        if ($deck->count() < self::STARTING_HAND_SIZE) {
            throw new NotEnoughCardsToStartCombatException();
        }

        $deck->shuffle();

        foreach ($deck->getAll() as $card) {
            $this->drawPile->add($card);
        }

        for ($i = 0; $i < self::STARTING_HAND_SIZE; $i++) {
            $this->player->getHand()->add(
                $this->drawPile->takeFirst()
            );
        }
    }

    private function startCurrentTurn(): void
    {
        while (!$this->isEnded()) {
            $combatant = $this->getCurrentCombatant();

            $combatant->processStatuses();
            $this->checkCombatEnd();

            if ($this->isEnded()) {
                return;
            }

            if (!$combatant->isAlive()) {
                $this->moveToNextAliveCombatant();

                continue;
            }

            if ($combatant instanceof Player) {
                $this->drawCardFor($combatant);
                $combatant->restoreManaToMax();

                return;
            }

            $this->checkCombatEnd();

            if ($this->isEnded()) {
                return;
            }

            $this->moveToNextAliveCombatant();
        }
    }

    private function getCurrentCombatant(): CombatantInterface
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

    private function checkCombatEnd(): void
    {
        if (!$this->player->isAlive()) {
            $this->outcome = CombatOutcome::DEFEAT;

            return;
        }

        foreach ($this->enemies as $enemy) {
            if ($enemy->isAlive()) {
                return;
            }
        }

        $this->outcome = CombatOutcome::VICTORY;
    }

    public function isEnded(): bool
    {
        return $this->outcome !== null;
    }

    private function moveToNextAliveCombatant(): void
    {
        $combatantsCount = count($this->combatants);

        for ($i = 0; $i < $combatantsCount; $i++) {
            $this->currentCombatantIndex
                = ($this->currentCombatantIndex + 1) % $combatantsCount;

            if ($this->getCurrentCombatant()->isAlive()) {
                return;
            }
        }

        throw new NoAliveCombatantFoundException();
    }

    public function playCard(
        Player              $player,
        Card                $card,
        ?CombatantInterface $target = null
    ): void {
        $this->validatePlayCard($player, $card);

        /**
         * @var ResolvedEffect[] $resolvedEffects
         */
        $resolvedEffects = [];

        foreach ($card->getEffects() as $effect) {
            $resolvedEffects[] = new ResolvedEffect(
                $effect->effect,
                $effect->target->resolve($this, $player, $target)
            );
        }

        $player->takeMana($card->getCost());

        foreach ($resolvedEffects as $resolvedEffect) {
            $resolvedEffect->effect->apply($this, $resolvedEffect->targets);
        }

        $this->checkCombatEnd();

        $this->discardPile->add($player->getHand()->take($card));
    }

    private function validatePlayCard(
        Player $player,
        Card   $card
    ): void {
        if ($this->isEnded()) {
            throw new CombatAlreadyEndedException();
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
        if ($this->isEnded()) {
            throw new CombatAlreadyEndedException();
        }

        if ($this->getCurrentCombatant() !== $player) {
            throw new NotPlayerTurnException();
        }

        $this->moveToNextAliveCombatant();
        $this->startCurrentTurn();
    }

    public function getOutcome(): ?CombatOutcome
    {
        return $this->outcome;
    }

    /**
     * @return CombatantInterface[]
     */
    public function getOpponentsOf(
        CombatantInterface $combatant
    ): array {
        if ($combatant === $this->player) {
            return array_values(
                array_filter(
                    $this->enemies,
                    static fn(Enemy $enemy): bool => $enemy->isAlive(),
                )
            );
        }

        if (in_array($combatant, $this->enemies, true)) {
            return [$this->player];
        }

        throw new CombatantNotInCombatException();
    }

}
