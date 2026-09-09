<?php

declare(strict_types=1);

namespace WildDeck\Target;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

final class SelfTargetResolver implements TargetResolverInterface
{
    public function resolve(
        Game    $game,
        Player  $source,
        ?Player $selectedTarget,
    ): array
    {
        return [
            $source,
        ];
    }
}
