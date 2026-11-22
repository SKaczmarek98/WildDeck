<?php

declare(strict_types=1);

namespace WildDeck\Combatant;

use InvalidArgumentException;
use WildDeck\Status\StatusInterface;

abstract class AbstractCombatant implements CombatantInterface
{
    protected const int MIN_LIFE_POINTS = 0;

    protected string $name;
    protected int $lifePoints;
    protected int $maxLifePoints;

    /** @var StatusInterface[] */
    protected array $statuses = [];


    public function __construct(
        string $name,
        int    $maxLifePoints
    ) {
        $this->name = $name;
        $this->lifePoints = $maxLifePoints;
        $this->maxLifePoints = $maxLifePoints;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLifePoints(): int
    {
        return $this->lifePoints;
    }

    public function takeDamage(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException();
        }

        $this->lifePoints = max(
            static::MIN_LIFE_POINTS,
            $this->lifePoints - $amount,
        );
    }

    public function heal(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException();
        }

        $this->lifePoints = min(
            $this->maxLifePoints,
            $this->lifePoints + $amount,
        );
    }

    public function isAlive(): bool
    {
        return $this->lifePoints > 0;
    }

    public function addStatus(StatusInterface $status): void
    {
        foreach ($this->statuses as $existingStatus) {
            if ($existingStatus->tryStack($status)) {
                return;
            }
        }

        $this->statuses[] = $status;
    }

    public function processStatuses(): void
    {
        foreach ($this->statuses as $index => $status) {
            $status->onTurnStart($this);

            if ($status->isExpired()) {
                unset($this->statuses[$index]);
            }
        }
    }
}
