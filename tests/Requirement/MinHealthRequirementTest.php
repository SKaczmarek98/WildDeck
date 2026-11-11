<?php

declare(strict_types=1);

namespace WildDeck\Tests\Requirement;

use PHPUnit\Framework\TestCase;
use WildDeck\Game\Game;
use WildDeck\Game\Player;
use WildDeck\Requirement\MinHealthRequirement;
use WildDeck\Tests\TestHelper;

class MinHealthRequirementTest extends TestCase
{
    public function testMinHealthRequirementIsSatisfiedWhenPlayerHasEnoughLowHp(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $requirement = new MinHealthRequirement(10);
        $game = new Game([$alice, $bob], TestHelper::createDeck());

        $result = $requirement->check(
            $game,
            $alice,
        );

        self::assertTrue($result->satisfied);
        self::assertNull($result->reason);
    }

    public function testMinHealthRequirementReturnsReasonWhenPlayerHasTooLowHp(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');


        $requirement = new MinHealthRequirement(15);
        $game = new Game([$alice, $bob], TestHelper::createDeck());

        $alice->takeDamage(10);

        $result = $requirement->check(
            $game,
            $alice,
        );

        self::assertFalse($result->satisfied);
        self::assertSame('MIN_HEALTH_EXCEEDED', $result->reason);
    }
    
}
