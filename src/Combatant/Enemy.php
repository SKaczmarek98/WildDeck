<?php

declare(strict_types=1);

namespace WildDeck\Combatant;

class Enemy extends AbstractCombatant
{
    public function __construct(
        string      $name,
        int         $maxLifePoints,
        private int $attackDamage,
    ) {
        parent::__construct($name, $maxLifePoints);
    }

    public function attack(CombatantInterface $target): void
    {
        $target->takeDamage($this->attackDamage);
    }
}
