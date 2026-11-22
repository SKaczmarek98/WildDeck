<?php

declare(strict_types=1);

namespace WildDeck\Tests\Combat;

use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\Deck;
use WildDeck\Combat\Combat;
use WildDeck\Combat\Exception\CombatantNotInCombatException;
use WildDeck\Combat\Exception\NotEnoughCardsToStartCombatException;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;
use WildDeck\Tests\TestHelper;

final class CombatTest extends TestCase
{
    public function testCombatCannotStartWithFewerThanTwoCards(): void
    {
        $this->expectException(NotEnoughCardsToStartCombatException::class);

        new Combat(new Player('Hero'), [new Enemy('Enemy', 20, 0)], new Deck([
            new Card('Only card', []),
        ]));
    }

    public function testTwoCardsAreEnoughForTheStartingHand(): void
    {
        $player = new Player('Hero');
        $deck = TestHelper::createDeck();

        new Combat($player, [new Enemy('Enemy', 20, 0)], $deck);

        self::assertSame(2, $player->getHand()->count());
        foreach ($deck->getAll() as $card) {
            self::assertTrue($player->getHand()->contains($card));
        }
    }

    public function testFirstAndSubsequentPlayerTurnsDrawOneCardAndRestoreMana(): void
    {
        $player = new Player('Hero');
        $player->takeMana(2);
        $enemy = new Enemy('Enemy', 20, 0);
        $deck = new Deck([
            new Card('Filler 1', []),
            new Card('Filler 2', []),
            new Card('Filler 3', []),
            new Card('Filler 4', []),
        ]);

        $combat = new Combat($player, [$enemy], $deck);

        self::assertSame(3, $player->getHand()->count());
        self::assertSame(3, $player->getManaPoints());

        $player->takeMana(3);
        $combat->endTurn($player);

        self::assertSame(4, $player->getHand()->count());
        self::assertSame(3, $player->getManaPoints());
    }

    public function testOpponentsAreLivingEnemiesForThePlayerAndThePlayerForAnEnemy(): void
    {
        $player = new Player('Hero');
        $first = new Enemy('First', 20, 0);
        $dead = new Enemy('Dead', 20, 0);
        $dead->takeDamage(20);
        $last = new Enemy('Last', 20, 0);
        $combat = new Combat($player, [$first, $dead, $last], TestHelper::createDeck());

        self::assertSame([$first, $last], $combat->getOpponentsOf($player));
        self::assertSame([$player], $combat->getOpponentsOf($last));
    }

    public function testGettingOpponentsOfAnOutsiderThrows(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $this->expectException(CombatantNotInCombatException::class);
        $combat->getOpponentsOf(new Enemy('Enemy', 20, 0));
    }
}
