<?php

declare(strict_types=1);

namespace WildDeck\Tests\Status;

use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\CardEffect;
use WildDeck\Combatant\Player;
use WildDeck\Effect\ApplyStatusEffect;
use WildDeck\Combat\Game;
use WildDeck\Status\BurnStatus;
use WildDeck\Status\PoisonStatus;
use WildDeck\Status\RegenerationStatus;
use WildDeck\Target\SelectedOpponentTarget;
use WildDeck\Target\SelfTargetResolver;
use WildDeck\Tests\TestHelper;

final class StatusLifecycleTest extends TestCase
{
    public function testBurnActsOnlyAtStartOfSelectedTargetsTurn(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $card = new Card('Burn', [
            new CardEffect(new ApplyStatusEffect(new BurnStatus(2, 3)), new SelectedOpponentTarget()),
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

    public function testBurnActsExactlyForItsDurationAndStopsDamaging(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $card = new Card('Burn', [
            new CardEffect(new ApplyStatusEffect(new BurnStatus(2, 3)), new SelectedOpponentTarget()),
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

    public function testPoisonDealsDecreasingDamageUntilItExpires(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $game = new Game([$alice, $bob], TestHelper::createDeck());

        $bob->addStatus(new PoisonStatus(3));

        self::assertSame(20, $bob->getLifePoints());

        foreach ([17, 15, 14, 14] as $turn => $expectedHealth) {
            $game->endTurn($alice);

            self::assertSame($expectedHealth, $bob->getLifePoints(), 'Target turn ' . ($turn + 1));

            $game->endTurn($bob);

            self::assertSame($expectedHealth, $bob->getLifePoints());
        }
    }

    public function testReapplyingPoisonAddsItsDurationToTheExistingPoison(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $game = new Game([$alice, $bob], TestHelper::createDeck());

        $bob->addStatus(new PoisonStatus(3));
        $bob->addStatus(new PoisonStatus(2));

        self::assertSame(20, $bob->getLifePoints());

        foreach ([15, 11, 8, 6, 5, 5] as $turn => $expectedHealth) {
            $game->endTurn($alice);

            self::assertSame($expectedHealth, $bob->getLifePoints(), 'Target turn ' . ($turn + 1));

            $game->endTurn($bob);
        }
    }

    public function testBurnAndPoisonApplyAndExpireIndependently(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $game = new Game([$alice, $bob], TestHelper::createDeck());

        $bob->addStatus(new BurnStatus(2, 2));
        $bob->addStatus(new PoisonStatus(3));

        foreach ([15, 11, 10, 10] as $turn => $expectedHealth) {
            $game->endTurn($alice);

            self::assertSame($expectedHealth, $bob->getLifePoints(), 'Target turn ' . ($turn + 1));

            $game->endTurn($bob);
        }
    }

    public function testReapplyingBurnExtendsDurationAndKeepsOriginalDamage(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');
        $game = new Game([$alice, $bob], TestHelper::createDeck());

        $bob->addStatus(new BurnStatus(2, 3));
        $game->endTurn($alice);
        self::assertSame(18, $bob->getLifePoints());
        $game->endTurn($bob);

        $bob->addStatus(new BurnStatus(5, 2));

        self::assertSame(18, $bob->getLifePoints());

        foreach ([16, 14, 12, 10, 10] as $turn => $expectedHealth) {
            $game->endTurn($alice);

            self::assertSame($expectedHealth, $bob->getLifePoints(), 'Target turn ' . ($turn + 2));

            $game->endTurn($bob);
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
