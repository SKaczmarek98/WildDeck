<?php

declare(strict_types=1);

namespace WildDeck\Status;

use InvalidArgumentException;
use WildDeck\Game\Player;

abstract class AbstractStatus implements StatusInterface
{
    public function __construct(
        private int $remainingTurns,
    )
    {
        if ($remainingTurns <= 0) {
            throw new InvalidArgumentException(
                'Duration must be greater than zero.'
            );
        }
    }

    final public function onTurnStart(Player $player): void
    {
        $this->applyEffect($player);
        $this->remainingTurns--;
    }

    abstract protected function applyEffect(Player $player): void;

    final public function isExpired(): bool
    {
        return $this->remainingTurns <= 0;
    }

}
