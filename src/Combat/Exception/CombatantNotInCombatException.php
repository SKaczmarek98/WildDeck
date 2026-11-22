<?php

declare(strict_types=1);

namespace WildDeck\Combat\Exception;

use RuntimeException;

final class CombatantNotInCombatException extends RuntimeException
{

    public function __construct()
    {
        parent::__construct("The combatant is not in the game");
    }
}
