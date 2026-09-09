<?php

declare(strict_types=1);

namespace WildDeck\Game\Exception;

use RuntimeException;

final class NoAlivePlayerFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No alive player found.');
    }
}