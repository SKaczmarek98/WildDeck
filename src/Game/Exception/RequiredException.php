<?php

declare(strict_types=1);

namespace WildDeck\Game\Exception;

use RuntimeException;

final class RequiredException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}