<?php

declare(strict_types=1);

namespace WildDeck\Requirement;

final readonly class RequirementResult
{
    public function __construct(
        public bool    $satisfied,
        public ?string $reason = null,
    )
    {
    }
}

