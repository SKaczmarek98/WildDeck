<?php

declare(strict_types=1);

namespace WildDeck\Tests\Status;

use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\CardEffect;
use WildDeck\Combat\Combat;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;
use WildDeck\Effect\ApplyStatusEffect;
use WildDeck\Status\BurnStatus;
use WildDeck\Status\PoisonStatus;
use WildDeck\Status\RegenerationStatus;
use WildDeck\Target\SelectedOpponentTarget;
use WildDeck\Target\SelfTargetResolver;
use WildDeck\Tests\TestHelper;

final class StatusLifecycleTest extends TestCase
{
    public function testBurnCardDamagesTheEnemyOnItsTurnsForTheConfiguredDuration(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $card = new Card('Burn', [
            new CardEffect(new ApplyStatusEffect(new BurnStatus(2, 3)), new SelectedOpponentTarget()),
        ]);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));

        $combat->playCard($player, $card, $enemy);

        self::assertSame(20, $enemy->getLifePoints());

        foreach ([18, 16, 14, 14] as $expectedHealth) {
            $combat->endTurn($player);

            self::assertSame($expectedHealth, $enemy->getLifePoints());
            self::assertSame(20, $player->getLifePoints());
        }
    }

    public function testReapplyingPoisonAddsStacksAndDamageDecreasesUntilItExpires(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $player->addStatus(new PoisonStatus(3));
        $combat->endTurn($player);
        self::assertSame(17, $player->getLifePoints());

        $player->addStatus(new PoisonStatus(2));
        self::assertSame(17, $player->getLifePoints());

        foreach ([13, 10, 8, 7, 7] as $expectedHealth) {
            $combat->endTurn($player);

            self::assertSame($expectedHealth, $player->getLifePoints());
        }
    }

    public function testBurnAndPoisonApplyAndExpireIndependently(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $enemy->addStatus(new BurnStatus(2, 2));
        $enemy->addStatus(new PoisonStatus(3));

        foreach ([15, 11, 10, 10] as $expectedHealth) {
            $combat->endTurn($player);

            self::assertSame($expectedHealth, $enemy->getLifePoints());
        }
    }

    public function testReapplyingBurnExtendsDurationAndKeepsOriginalDamage(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $enemy->addStatus(new BurnStatus(2, 3));
        $combat->endTurn($player);
        self::assertSame(18, $enemy->getLifePoints());

        $enemy->addStatus(new BurnStatus(5, 2));
        self::assertSame(18, $enemy->getLifePoints());

        foreach ([16, 14, 12, 10, 10] as $expectedHealth) {
            $combat->endTurn($player);

            self::assertSame($expectedHealth, $enemy->getLifePoints());
        }
    }

    public function testRegenerationCardHealsOnPlayerTurnsAndExpires(): void
    {
        $player = new Player('Hero');
        $player->takeDamage(15);
        $enemy = new Enemy('Enemy', 20, 0);
        $card = new Card('Regeneration', [
            new CardEffect(new ApplyStatusEffect(new RegenerationStatus(2, 3)), new SelfTargetResolver()),
        ]);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));

        $combat->playCard($player, $card);
        self::assertSame(5, $player->getLifePoints());

        foreach ([7, 9, 11, 11] as $expectedHealth) {
            $combat->endTurn($player);

            self::assertSame($expectedHealth, $player->getLifePoints());
        }
    }

    public function testEnemyRegenerationCannotExceedMaximumHealth(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 12, 0);
        $enemy->takeDamage(1);
        $enemy->addStatus(new RegenerationStatus(3, 1));
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        self::assertSame(11, $enemy->getLifePoints());
        $combat->endTurn($player);

        self::assertSame(12, $enemy->getLifePoints());
    }
}
