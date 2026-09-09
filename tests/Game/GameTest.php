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
use WildDeck\Game\Exception\NotEnoughCardsToStartGameException;
use WildDeck\Game\Exception\NotPlayerTurnException;
use WildDeck\Game\Game;
use WildDeck\Game\Player;
use WildDeck\Target\SelectedOpponentTarget;
use WildDeck\Target\SelfTargetResolver;

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

    private function createFireballCard(int $amount = 5): Card
    {
        return new Card('Fireball', [
            new CardEffect(
                new DamageEffect($amount),
                new SelectedOpponentTarget()
            )
        ]);
    }

    private function createHealCard(int $amount = 5): Card
    {
        return new Card('Heal', [
            new CardEffect(
                new HealEffect($amount),
                new SelfTargetResolver()
            )
        ]);
    }


    public function testGameCannotStartWithoutEnoughCards(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $deck = new Deck([
                             $this->createFireballCard(),
                         ]);

        $this->expectException(NotEnoughCardsToStartGameException::class);
        new Game([$alice, $bob], $deck);
    }

    public function testDamageCardDealsDamageToOpponent(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = $this->createFireballCard();

        $deck = $this->createDeck($fireball);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $fireball, $bob);

        self::assertSame(15, $bob->getLifePoints());
    }

    public function testOtherPlayerCannotPlayCardDuringOpponentTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = new Card('Alice card', []);
        $bobCard = $this->createFireballCard();

        $deck = $this->createDeck($aliceCard, $bobCard);

        $game = new Game([$alice, $bob], $deck);

        $this->expectException(NotPlayerTurnException::class);

        $game->playCard($bob, $bobCard, $alice);

    }

    public function testCardIsRemovedFromHandAfterPlay(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = $this->createFireballCard();
        $deck = $this->createDeck($fireball);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $fireball, $bob);

        self::assertFalse($alice->getHand()->contains($fireball));
    }

    public function testGameEndsWhenOpponentDies(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $fireball = $this->createFireballCard(20);
        $deck = $this->createDeck($fireball);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $fireball, $bob);

        self::assertTrue($game->isEnded());
        self::assertSame($alice, $game->getWinner());
    }

    public function testHealCardHealsDamageToPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $heal = $this->createHealCard();
        $deck = $this->createDeck($heal);

        $game = new Game([$alice, $bob], $deck);
        $alice->takeDamage(10);
        $game->playCard($alice, $heal);

        self::assertSame(15, $alice->getLifePoints());
    }

    public function testOtherPlayerCannotEndTurnDuringOpponentTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $aliceCard = $this->createFireballCard();
        $deck = $this->createDeck($aliceCard);

        $game = new Game([$alice, $bob], $deck);

        $this->expectException(NotPlayerTurnException::class);

        $game->endTurn($bob);
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

        $deck = $this->createDeck($aliceCard);

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

        $deck = $this->createDeck($aliceCard);
        $deck->add($drawCard1);
        $deck->add($drawCard2);

        $game = new Game([$alice, $bob], $deck);
        $game->playCard($alice, $aliceCard);

        self::assertSame(3, $alice->getHand()->count());
        self::assertTrue($alice->getHand()->contains($drawCard1));
        self::assertTrue($alice->getHand()->contains($drawCard2));
    }

    public function testChangeCurrentPlayer(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $josh = new Player('Josh');

        $aliceCard = $this->createFireballCard();
        $aliceCard2 = $this->createFireballCard();
        $bobCard = $this->createFireballCard();
        $joshCard = $this->createFireballCard();
        $deck = new Deck([
                             $aliceCard,
                             $bobCard,
                             $joshCard,
                             $aliceCard2,
                             new Card('Draw card 2', []),
                             new Card('Draw card 2', []),
        ]);

        $game = new Game([$alice, $bob, $josh], $deck);
        $game->playCard($alice, $aliceCard, $bob);
        $game->endTurn($alice);

        $game->playCard($bob, $bobCard, $josh);
        $game->endTurn($bob);

        $game->playCard($josh, $joshCard, $bob);
        $game->endTurn($josh);

        $game->playCard($alice, $aliceCard2, $bob);

    }
}
