<?php

declare(strict_types=1);

namespace WildDeck\Tests\Status;

use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\CardEffect;
use WildDeck\Effect\ApplyStatusEffect;
use WildDeck\Game\Game;
use WildDeck\Game\Player;
use WildDeck\Status\PoisonStatus;
use WildDeck\Status\RegenerationStatus;
use WildDeck\Target\SelectedOpponentTarget;
use WildDeck\Target\SelfTargetResolver;
use WildDeck\Tests\TestHelper;

final class StatusLifecycleTest extends TestCase
{
    public function testPoisonActsOnlyAtStartOfSelectedTargetsTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $card = new Card('Poison', [
            new CardEffect(new ApplyStatusEffect(new PoisonStatus(2, 3)), new SelectedOpponentTarget()),
        ]);
        $game = new Game([$alice, $bob], TestHelper::createDeck($card));

        $game->playCard($alice, $card, $bob);

        self::assertSame(20, $bob->getLifePoints());
        self::assertSame(20, $alice->getLifePoints());

        $game->endTurn($alice);

        self::assertSame(18, $bob->getLifePoints());
        self::assertSame(20, $alice->getLifePoints());

        $game->endTurn($bob);

        self::assertSame(18, $bob->getLifePoints());
        self::assertSame(20, $alice->getLifePoints());

        $game->endTurn($alice);

        self::assertSame(16, $bob->getLifePoints());
        self::assertSame(20, $alice->getLifePoints());
    }

    public function testPoisonActsExactlyForItsDurationAndStopsDamaging(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $card = new Card('Poison', [
            new CardEffect(new ApplyStatusEffect(new PoisonStatus(2, 3)), new SelectedOpponentTarget()),
        ]);
        $game = new Game([$alice, $bob], TestHelper::createDeck($card));

        $game->playCard($alice, $card, $bob);

        // Three active target turns followed by two turns after expiration.
        foreach ([18, 16, 14, 14, 14] as $expectedHealth) {
            $game->endTurn($alice);

            self::assertSame($expectedHealth, $bob->getLifePoints());
            self::assertSame(20, $alice->getLifePoints());

            $game->endTurn($bob);

            self::assertSame($expectedHealth, $bob->getLifePoints());
            self::assertSame(20, $alice->getLifePoints());
        }
    }

    public function testRegenerationActsOnlyAtStartOfOwnersTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $alice->takeDamage(10);
        $bob->takeDamage(10);
        $card = new Card('Regeneration', [
            new CardEffect(new ApplyStatusEffect(new RegenerationStatus(2, 3)), new SelfTargetResolver()),
        ]);
        $game = new Game([$alice, $bob], TestHelper::createDeck($card));

        $game->playCard($alice, $card);

        self::assertSame(10, $alice->getLifePoints());
        self::assertSame(10, $bob->getLifePoints());

        $game->endTurn($alice);

        self::assertSame(10, $alice->getLifePoints());
        self::assertSame(10, $bob->getLifePoints());

        $game->endTurn($bob);

        self::assertSame(12, $alice->getLifePoints());
        self::assertSame(10, $bob->getLifePoints());
    }

    public function testRegenerationActsExactlyForItsDurationAndStopsHealing(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $alice->takeDamage(15);
        $bob->takeDamage(10);
        $card = new Card('Regeneration', [
            new CardEffect(new ApplyStatusEffect(new RegenerationStatus(2, 3)), new SelfTargetResolver()),
        ]);
        $game = new Game([$alice, $bob], TestHelper::createDeck($card));

        $game->playCard($alice, $card);

        // Stay below maximum HP so the healing cap cannot hide an extra activation.
        foreach ([7, 9, 11, 11, 11] as $expectedHealth) {
            $healthBeforeOpponentTurn = $alice->getLifePoints();
            $game->endTurn($alice);

            self::assertSame($healthBeforeOpponentTurn, $alice->getLifePoints());
            self::assertSame(10, $bob->getLifePoints());

            $game->endTurn($bob);

            self::assertSame($expectedHealth, $alice->getLifePoints());
            self::assertSame(10, $bob->getLifePoints());
        }
    }

    public function testRegenerationDoesNotExceedMaximumHealth(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $alice->takeDamage(1);
        $card = new Card('Regeneration', [
            new CardEffect(new ApplyStatusEffect(new RegenerationStatus(3, 2)), new SelfTargetResolver()),
        ]);
        $game = new Game([$alice, $bob], TestHelper::createDeck($card));

        $game->playCard($alice, $card);

        self::assertSame(19, $alice->getLifePoints());

        for ($turn = 0; $turn < 2; $turn++) {
            $game->endTurn($alice);
            $game->endTurn($bob);

            self::assertSame(20, $alice->getLifePoints());
        }
    }
}
