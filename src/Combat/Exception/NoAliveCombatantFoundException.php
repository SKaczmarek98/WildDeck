<?php

declare(strict_types=1);

namespace WildDeck\Combat\Exception;

use RuntimeException;

final class NoAliveCombatantFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No alive combatant found.');
    }
}
