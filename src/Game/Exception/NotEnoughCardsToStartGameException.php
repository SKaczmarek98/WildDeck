<?php

declare(strict_types=1);

namespace WildDeck\Game\Exception;

use RuntimeException;

final class NotEnoughCardsToStartGameException extends RuntimeException
{

    public function __construct()
    {
        parent::__construct("Not enough cards to start game");
    }
}