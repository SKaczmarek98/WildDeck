<?php

declare(strict_types=1);

namespace WildDeck\Target;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Game\CombatInterface;

final class SelfTargetResolver implements TargetResolverInterface
{
    public function resolve(
        CombatInterface     $combat,
        CombatantInterface  $source,
        ?CombatantInterface $selectedTarget,
    ): array {
        return [
            $source,
        ];
    }
}
