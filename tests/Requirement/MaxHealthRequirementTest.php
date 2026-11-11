<?php

declare(strict_types=1);

namespace WildDeck\Tests\Requirement;

use PHPUnit\Framework\TestCase;
use WildDeck\Game\Game;
use WildDeck\Game\Player;
use WildDeck\Requirement\MaxHealthRequirement;
use WildDeck\Tests\TestHelper;

class MaxHealthRequirementTest extends TestCase
{
    public function testMaxHealthRequirementIsSatisfiedWhenPlayerHasEnoughLowHp(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $alice->takeDamage(10);

        $requirement = new MaxHealthRequirement(10);
        $game = new Game([$alice, $bob], TestHelper::createDeck());

        $result = $requirement->check(
            $game,
            $alice,
        );

        self::assertTrue($result->satisfied);
        self::assertNull($result->reason);
    }

    public function testMaxHealthRequirementReturnsReasonWhenPlayerHasTooMuchHp(): void
    {
        $alice = new Player('Alice');
        $bob = new Player('Bob');

        $requirement = new MaxHealthRequirement(10);
        $game = new Game([$alice, $bob], TestHelper::createDeck());


        $result = $requirement->check(
            $game,
            $alice,
        );

        self::assertFalse($result->satisfied);
        self::assertSame('MAX_HEALTH_EXCEEDED', $result->reason);
    }
    
}
