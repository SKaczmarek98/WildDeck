<?php

declare(strict_types=1);

namespace WildDeck\Target;

use WildDeck\Combatant\Combatant;
use WildDeck\Game\Game;

final class SelfTargetResolver implements TargetResolverInterface
{
    public function resolve(
        Game       $game,
        Combatant  $source,
        ?Combatant $selectedTarget,
    ): array
    {
        return [
            $source,
        ];
    }
}
