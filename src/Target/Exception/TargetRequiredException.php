<?php

declare(strict_types=1);

namespace WildDeck\Target\Exception;

use RuntimeException;

final class TargetRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Target is required');
    }
}
