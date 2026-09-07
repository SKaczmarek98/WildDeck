<?php

declare(strict_types=1);

namespace WildDeck\Tests\Game;

use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\Deck;
use WildDeck\Effect\DamageEffect;
use WildDeck\Effect\DrawEffect;
use WildDeck\Effect\HealEffect;
use WildDeck\Game\Exception\NotEnoughCardsToStartGameException;
use WildDeck\Game\Exception\NotPlayerTurnException;
use WildDeck\Game\Game;
use WildDeck\Game\Player;

class GameTest extends TestCase
{

    private function createDeck(
        Card  $aliceCard,
        ?Card $bobCard = null,
    ): Deck
    {
        return new Deck([
                            $aliceCard,
                            $bobCard ?? new Card('Bob filler 1', []),
                            new Card('Alice filler 2', []),
                            new Card('Bob filler 2', []),
                        ]);
    }

    public function testGameCannotStartWithoutEnoughCards(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $deck = new Deck([
                             new Card('Fireball', [new DamageEffect(5)]),
                         ]);

        $this->expectException(NotEnoughCardsToStartGameException::class);
        new Game($alice, $bob, $deck);
    }

    public function testDamageCardDealsDamageToOpponent(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = new Card('Fireball', [new DamageEffect(5)]);

        $deck = $this->createDeck($fireball);

        $game = new Game($alice, $bob, $deck);
        $game->playCard($alice, $fireball);

        self::assertSame(15, $bob->getLifePoints());
    }

    public function testOtherPlayerCannotPlayCardDuringOpponentTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Alice card', []);
        $bobCard = new Card('Bob Fireball', [new DamageEffect(5)]);

        $deck = $this->createDeck($aliceCard, $bobCard);

        $game = new Game($alice, $bob, $deck);

        $this->expectException(NotPlayerTurnException::class);

        $game->playCard($bob, $bobCard);

    }

    public function testCardIsRemovedFromHandAfterPlay(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = new Card('Fireball', [new DamageEffect(5)]);
        $deck = $this->createDeck($fireball);

        $game = new Game($alice, $bob, $deck);
        $game->playCard($alice, $fireball);

        self::assertFalse($alice->getHand()->contains($fireball));
    }

    public function testGameEndsWhenOpponentDies(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = new Card('Fireball', [new DamageEffect(20)]);
        $deck = $this->createDeck($fireball);

        $game = new Game($alice, $bob, $deck);
        $game->playCard($alice, $fireball);

        self::assertTrue($game->isEnded());
        self::assertSame($alice, $game->getWinner());
    }

    public function testHealCardHealsDamageToPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $heal = new Card('Heal', [new HealEffect(5)]);
        $deck = $this->createDeck($heal);

        $game = new Game($alice, $bob, $deck);
        $alice->takeDamage(10);
        $game->playCard($alice, $heal);

        self::assertSame(15, $alice->getLifePoints());
    }

    public function testOtherPlayerCannotEndTurnDuringOpponentTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Fireball', [new DamageEffect(5)]);
        $deck = $this->createDeck($aliceCard);

        $game = new Game($alice, $bob, $deck);

        $this->expectException(NotPlayerTurnException::class);

        $game->endTurn($bob);
    }

    public function testDrainCardDamagesOpponentAndHealsPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Drain', [
            new DamageEffect(3),
            new HealEffect(3),
        ]);

        $deck = $this->createDeck($aliceCard);

        $game = new Game($alice, $bob, $deck);
        $alice->takeDamage(10);
        $game->playCard($alice, $aliceCard);

        self::assertSame(13, $alice->getLifePoints());
        self::assertSame(17, $bob->getLifePoints());
    }

    public function testDrawCardAddedCardToPlayersHand(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Drain', [
            new DrawEffect(2),
        ]);
        $drawCard1 = new Card('Draw card 1', []);
        $drawCard2 = new Card('Draw card 2', []);

        $deck = $this->createDeck($aliceCard);
        $deck->add($drawCard1);
        $deck->add($drawCard2);

        $game = new Game($alice, $bob, $deck);
        $game->playCard($alice, $aliceCard);

        self::assertSame(3, $alice->getHand()->count());
        self::assertTrue($alice->getHand()->contains($drawCard1));
        self::assertTrue($alice->getHand()->contains($drawCard2));
    }
}
