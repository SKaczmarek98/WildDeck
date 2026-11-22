<?php

declare(strict_types=1);

namespace WildDeck\Combat\Exception;

use RuntimeException;

final class NotEnoughCardsToStartCombatException extends RuntimeException
{

    public function __construct()
    {
        parent::__construct("Not enough cards to start combat");
    }
}
