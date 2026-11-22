<?php

declare(strict_types=1);

namespace WildDeck\Effect;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combat\CombatInterface;

interface EffectInterface
{

    /**
     * @param CombatantInterface[] $targets
     */
    public function apply(CombatInterface $combat, array $targets): void;

}
