<?php

declare(strict_types=1);

namespace WildDeck\Combat\Exception;

use RuntimeException;

final class CombatAlreadyEndedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Combat is already ended.');
    }
}
