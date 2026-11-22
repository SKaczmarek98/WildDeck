<?php

declare(strict_types=1);

namespace WildDeck\Tests\Requirement;

use PHPUnit\Framework\TestCase;
use WildDeck\Combat\Combat;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;
use WildDeck\Requirement\MinHealthRequirement;
use WildDeck\Tests\TestHelper;

class MinHealthRequirementTest extends TestCase
{
    public function testMinHealthRequirementIsSatisfiedAtTheHealthThreshold(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);

        $player->takeDamage(10);

        $requirement = new MinHealthRequirement(10);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $result = $requirement->check(
            $combat,
            $player,
        );

        self::assertTrue($result->satisfied);
        self::assertNull($result->reason);
    }

    public function testMinHealthRequirementReturnsReasonWhenPlayerHasTooLowHp(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);

        $requirement = new MinHealthRequirement(15);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck());

        $player->takeDamage(10);

        $result = $requirement->check(
            $combat,
            $player,
        );

        self::assertFalse($result->satisfied);
        self::assertSame('MIN_HEALTH_EXCEEDED', $result->reason);
    }
}
