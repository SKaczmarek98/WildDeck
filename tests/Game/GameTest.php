<?php

declare(strict_types=1);

namespace WildDeck\Tests\Game;

use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\CardEffect;
use WildDeck\Cards\Deck;
use WildDeck\Effect\DamageEffect;
use WildDeck\Effect\DrawEffect;
use WildDeck\Effect\HealEffect;
use WildDeck\Game\Exception\NoManaToPlayCardException;
use WildDeck\Game\Exception\NotEnoughCardsToStartGameException;
use WildDeck\Game\Exception\NotPlayerTurnException;
use WildDeck\Game\Exception\RequirementNotSatisfiedException;
use WildDeck\Game\Game;
use WildDeck\Game\Player;
use WildDeck\Requirement\MaxHealthRequirement;
use WildDeck\Target\SelectedOpponentTarget;
use WildDeck\Target\SelfTargetResolver;
use WildDeck\Tests\TestHelper;

class GameTest extends TestCase
{

    public function testGameCannotStartWithoutEnoughCards(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $deck = new Deck([
                             TestHelper::createFireballCard(),
                         ]);

        $this->expectException(NotEnoughCardsToStartGameException::class);
        new Game([$alice, $bob], $deck);
    }

    public function testDamageCardDealsDamageToOpponent(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = TestHelper::createFireballCard();

        $deck = TestHelper::createDeck($fireball);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $fireball, $bob);

        self::assertSame(15, $bob->getLifePoints());
    }

    public function testOtherPlayerCannotPlayCardDuringOpponentTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Alice card', []);
        $bobCard = TestHelper::createFireballCard();

        $deck = TestHelper::createDeck($aliceCard, $bobCard);

        $game = new Game([$alice, $bob], $deck);

        $this->expectException(NotPlayerTurnException::class);

        $game->playCard($bob, $bobCard, $alice);

    }

    public function testCardIsRemovedFromHandAfterPlay(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = TestHelper::createFireballCard();
        $deck = TestHelper::createDeck($fireball);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $fireball, $bob);

        self::assertFalse($alice->getHand()->contains($fireball));
    }

    public function testGameEndsWhenOpponentDies(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = TestHelper::createFireballCard(20);
        $deck = TestHelper::createDeck($fireball);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $fireball, $bob);

        self::assertTrue($game->isEnded());
        self::assertSame($alice, $game->getWinner());
    }

    public function testHealCardHealsDamageToPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $heal = TestHelper::createHealCard();
        $deck = TestHelper::createDeck($heal);

        $game = new Game([$alice, $bob], $deck);
        $alice->takeDamage(10);
        $game->playCard($alice, $heal);

        self::assertSame(15, $alice->getLifePoints());
    }

    public function testOtherPlayerCannotEndTurnDuringOpponentTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = TestHelper::createFireballCard();
        $deck = TestHelper::createDeck($aliceCard);

        $game = new Game([$alice, $bob], $deck);

        $this->expectException(NotPlayerTurnException::class);

        $game->endTurn($bob);
    }

    public function testStartingNextTurnDrawsExactlyOneCardForNextPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $firstDraw = new Card('First draw', []);
        $secondDraw = new Card('Second draw', []);
        $deck = TestHelper::createDeck();
        $deck->add($firstDraw);
        $deck->add($secondDraw);
        $game = new Game([$alice, $bob], $deck);

        self::assertSame(3, $alice->getHand()->count());
        self::assertTrue($alice->getHand()->contains($firstDraw));

        $game->endTurn($alice);

        self::assertSame(3, $bob->getHand()->count());
        self::assertTrue($bob->getHand()->contains($secondDraw));
    }

    public function testDrainCardDamagesOpponentAndHealsPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Drain', [
            new CardEffect(
                new DamageEffect(3),
                new SelectedOpponentTarget()
            ),
            new CardEffect(
                new HealEffect(3),
                new SelfTargetResolver()
            ),
        ]);

        $deck = TestHelper::createDeck($aliceCard);

        $game = new Game([$alice, $bob], $deck);
        $alice->takeDamage(10);
        $game->playCard($alice, $aliceCard, $bob);

        self::assertSame(13, $alice->getLifePoints());
        self::assertSame(17, $bob->getLifePoints());
    }

    public function testDrawCardAddedCardToPlayersHand(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Draw', [
            new CardEffect(
                new DrawEffect(2),
                new SelfTargetResolver()
            )
        ]);
        $drawCard1 = new Card('Draw card 1', []);
        $drawCard2 = new Card('Draw card 2', []);

        $deck = TestHelper::createDeck($aliceCard);
        $deck->add($drawCard1);
        $deck->add($drawCard2);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $aliceCard);

        self::assertSame(3, $alice->getHand()->count());
        self::assertTrue($alice->getHand()->contains($drawCard1));
        self::assertTrue($alice->getHand()->contains($drawCard2));
    }

    public function testPlayersTakeTurnsInOrderAndCycleBackToFirstPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $josh = new Player('Josh');

        $aliceCard = TestHelper::createFireballCard();
        $aliceCard2 = TestHelper::createFireballCard();
        $bobCard = TestHelper::createFireballCard();
        $joshCard = TestHelper::createFireballCard();
        $deck = new Deck([
                             $aliceCard,
                             $bobCard,
                             $joshCard,
                             $aliceCard2,
                             new Card('Card 1', []),
                             new Card('Card 2', []),
                         ]);

        $game = new Game([$alice, $bob, $josh], $deck);

        $game->playCard($alice, $aliceCard, $bob);
        $game->endTurn($alice);

        $game->playCard($bob, $bobCard, $josh);
        $game->endTurn($bob);

        $game->playCard($josh, $joshCard, $bob);
        $game->endTurn($josh);

        $game->playCard($alice, $aliceCard2, $bob);

        self::assertSame(5, $bob->getLifePoints());
        self::assertSame(15, $josh->getLifePoints());
        self::assertSame(20, $alice->getLifePoints());
    }

    public function testPlayerCannotPlayCardWithoutEnoughMana(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = TestHelper::createFireballCard(cost: 4);
        $deck = TestHelper::createDeck($aliceCard);

        $game = new Game([$alice, $bob], $deck);

        $this->expectException(NoManaToPlayCardException::class);
        $game->playCard($alice, $aliceCard, $bob);
    }

    public function testPlayCardConsumeMana(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = TestHelper::createFireballCard(cost: 1);
        $deck = TestHelper::createDeck($aliceCard);

        $game = new Game([$alice, $bob], $deck);

        $game->playCard($alice, $aliceCard, $bob);

        self::assertSame(2, $alice->getManaPoints());
    }

    public function testUnmetMaxHealthRequirementRejectsCardWithoutChangingGameState(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $card = new Card(
                          'Low health fireball',
                          [new CardEffect(new DamageEffect(5), new SelectedOpponentTarget())],
            cost: 1,
            requirements: [new MaxHealthRequirement(10)],
        );

        $game = new Game([$alice, $bob], TestHelper::createDeck($card));

        $manaBefore = $alice->getManaPoints();
        $handBefore = $alice->getHand()->getAll();
        $targetHealthBefore = $bob->getLifePoints();

        try {
            $game->playCard($alice, $card, $bob);

            self::fail('An unmet card requirement should reject the play.');
        } catch (RequirementNotSatisfiedException $exception) {
            self::assertSame(
                'MAX_HEALTH_EXCEEDED',
                $exception->getMessage(),
            );
        }

        self::assertSame($manaBefore, $alice->getManaPoints());
        self::assertSame($targetHealthBefore, $bob->getLifePoints());
        self::assertSame($handBefore, $alice->getHand()->getAll());
    }



}
