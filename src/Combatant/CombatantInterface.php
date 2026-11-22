<?php

namespace WildDeck\Combatant;

use WildDeck\Status\StatusInterface;

interface CombatantInterface
{
    public function getName(): string;

    public function getLifePoints(): int;

    public function takeDamage(int $amount): void;

    public function heal(int $amount): void;

    public function isAlive(): bool;

    public function addStatus(StatusInterface $status): void;

    public function processStatuses(): void;
}
