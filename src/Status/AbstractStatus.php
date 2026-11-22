<?php

declare(strict_types=1);

namespace WildDeck\Status;

use InvalidArgumentException;
use WildDeck\Combatant\Combatant;

abstract class AbstractStatus implements StatusInterface
{
    public function __construct(
        protected int $remainingTurns,
    )
    {
        if ($remainingTurns <= 0) {
            throw new InvalidArgumentException(
                'Duration must be greater than zero.'
            );
        }
    }

    final public function onTurnStart(Combatant $player): void
    {
        $this->applyEffect($player);
        $this->remainingTurns--;
    }

    abstract protected function applyEffect(Combatant $player): void;

    public function isExpired(): bool
    {
        return $this->remainingTurns <= 0;
    }

    public function tryStack(StatusInterface $status): bool
    {
        if ($status::class !== static::class) {
            return false;
        }

        $this->remainingTurns += $status->remainingTurns;

        return true;
    }

}
