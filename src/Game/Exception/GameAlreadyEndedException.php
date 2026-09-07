<?php

declare(strict_types=1);

namespace WildDeck\Game\Exception;

use RuntimeException;

final class GameAlreadyEndedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Game is already ended.');
    }
}