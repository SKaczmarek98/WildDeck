<?php

declare(strict_types=1);

namespace WildDeck\Tests\Requirement;

use PHPUnit\Framework\TestCase;
use WildDeck\Combat\Combat;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;
use WildDeck\Requirement\MaxHealthRequirement;
use WildDeck\Tests\TestHelper;

class MaxHealthRequirementTest extends TestCase
{
    public function testMaxHealthRequirementIsSatisfiedAtTheHealthThreshold(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);

        $player->takeDamage(10);

        $requirement = new MaxHealthRequirement(10);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $result = $requirement->check(
            $combat,
            $player,
        );

        self::assertTrue($result->satisfied);
        self::assertNull($result->reason);
    }

    public function testMaxHealthRequirementReturnsReasonWhenPlayerHasTooMuchHp(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);

        $requirement = new MaxHealthRequirement(10);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $result = $requirement->check(
            $combat,
            $player,
        );

        self::assertFalse($result->satisfied);
        self::assertSame('MAX_HEALTH_EXCEEDED', $result->reason);
    }
}
