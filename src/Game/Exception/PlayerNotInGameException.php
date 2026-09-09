<?php

declare(strict_types=1);

namespace WildDeck\Game\Exception;

use RuntimeException;

final class PlayerNotInGameException extends RuntimeException
{

    public function __construct()
    {
        parent::__construct("The player is not in the game");
    }
}
