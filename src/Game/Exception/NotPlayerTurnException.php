<?php

declare(strict_types=1);

namespace WildDeck\Game\Exception;

use RuntimeException;

final class NotPlayerTurnException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('It is not this player\'s turn.');
    }
}