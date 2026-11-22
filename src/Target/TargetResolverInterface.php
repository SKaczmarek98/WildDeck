<?php

namespace WildDeck\Target;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combat\CombatInterface;

interface TargetResolverInterface
{

    /**
     * @return CombatantInterface[]
     */
    public function resolve(
        CombatInterface     $combat,
        CombatantInterface  $source,
        ?CombatantInterface $selectedTarget,
    ): array;
}
