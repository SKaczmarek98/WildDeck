<?php

declare(strict_types=1);

namespace WildDeck\Tests\Combat;

use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\Deck;
use WildDeck\Combat\Combat;
use WildDeck\Combat\CombatOutcome;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;
use WildDeck\Status\BurnStatus;
use WildDeck\Tests\TestHelper;

final class CombatTurnFlowTest extends TestCase
{
    public function testEachEnemyAttacksOnceBeforeThePlayerCanPlayAgain(): void
    {
        $player = new Player('Hero');
        $first = new Enemy('First', 20, 2);
        $second = new Enemy('Second', 20, 3);
        $card = TestHelper::createFireballCard();
        $combat = new Combat($player, [$first, $second], TestHelper::createDeck($card));

        $combat->endTurn($player);

        self::assertSame(15, $player->getLifePoints());

        $combat->playCard($player, $card, $first);

        self::assertSame(15, $first->getLifePoints());
    }

    public function testDeadEnemyIsSkipped(): void
    {
        $player = new Player('Hero');
        $dead = new Enemy('Dead', 20, 2);
        $living = new Enemy('Living', 20, 3);
        $combat = new Combat($player, [$dead, $living], TestHelper::createDeck());
        $dead->takeDamage(20);

        $combat->endTurn($player);

        self::assertSame(17, $player->getLifePoints());
        self::assertFalse($combat->isEnded());
    }

    public function testEnemyKilledByStatusAtTurnStartDoesNotAttack(): void
    {
        $player = new Player('Hero');
        $first = new Enemy('First', 2, 4);
        $second = new Enemy('Second', 20, 3);
        $card = TestHelper::createFireballCard();
        $combat = new Combat($player, [$first, $second], TestHelper::createDeck($card));
        $first->addStatus(new BurnStatus(2, 1));

        $combat->endTurn($player);

        self::assertFalse($first->isAlive());
        self::assertSame(17, $player->getLifePoints());
        self::assertFalse($combat->isEnded());

        $combat->playCard($player, $card, $second);

        self::assertSame(15, $second->getLifePoints());
    }

    public function testKillingTheFinalLivingEnemyEndsCombatInVictory(): void
    {
        $player = new Player('Hero');
        $first = new Enemy('First', 3, 0);
        $last = new Enemy('Last', 3, 0);
        $firstStrike = TestHelper::createFireballCard(3);
        $lastStrike = TestHelper::createFireballCard(3);
        $combat = new Combat($player, [$first, $last], new Deck([$firstStrike, $lastStrike]));

        $combat->playCard($player, $firstStrike, $first);

        self::assertFalse($combat->isEnded());
        self::assertNull($combat->getOutcome());

        $combat->playCard($player, $lastStrike, $last);

        self::assertFalse($last->isAlive());
        self::assertTrue($combat->isEnded());
        self::assertSame(CombatOutcome::VICTORY, $combat->getOutcome());
    }

    public function testLethalStatusOnTheFinalEnemyEndsCombatBeforeItAttacks(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 2, 4);
        $enemy->addStatus(new BurnStatus(2, 1));
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $combat->endTurn($player);

        self::assertFalse($enemy->isAlive());
        self::assertSame(20, $player->getLifePoints());
        self::assertTrue($combat->isEnded());
        self::assertSame(CombatOutcome::VICTORY, $combat->getOutcome());
    }

    public function testLethalStatusOnThePlayerEndsCombatBeforeDrawingOrRestoringMana(): void
    {
        $player = new Player('Hero');
        $player->takeDamage(17);
        $enemy = new Enemy('Enemy', 20, 1);
        $deck = new Deck([
            new Card('Filler 1', []),
            new Card('Filler 2', []),
            new Card('Filler 3', []),
            new Card('Filler 4', []),
        ]);
        $combat = new Combat($player, [$enemy], $deck);
        $player->takeMana(2);
        $player->addStatus(new BurnStatus(2, 1));
        $handBefore = $player->getHand()->getAll();

        $combat->endTurn($player);

        self::assertFalse($player->isAlive());
        self::assertTrue($combat->isEnded());
        self::assertSame(CombatOutcome::DEFEAT, $combat->getOutcome());
        self::assertSame($handBefore, $player->getHand()->getAll());
        self::assertSame(1, $player->getManaPoints());
    }

    public function testLethalEnemyAttackEndsCombatBeforeTheNextEnemyTurn(): void
    {
        $player = new Player('Hero');
        $player->takeDamage(18);
        $first = new Enemy('First', 20, 2);
        $second = new Enemy('Second', 20, 3);
        $second->addStatus(new BurnStatus(1, 1));
        $combat = new Combat($player, [$first, $second], TestHelper::createDeck());

        $combat->endTurn($player);

        self::assertSame(0, $player->getLifePoints());
        // The Player's health is capped at zero, so check for a second turn via Burn.
        self::assertSame(20, $second->getLifePoints());
        self::assertTrue($combat->isEnded());
        self::assertSame(CombatOutcome::DEFEAT, $combat->getOutcome());
    }
}
