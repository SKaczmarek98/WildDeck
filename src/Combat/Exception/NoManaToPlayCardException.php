<?php

declare(strict_types=1);

namespace WildDeck\Combat\Exception;

use RuntimeException;

final class NoManaToPlayCardException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No mana to play card exception.');
    }
}
