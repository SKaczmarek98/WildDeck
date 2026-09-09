<?php

declare(strict_types=1);

namespace WildDeck\Target;

use WildDeck\Game\Game;
use WildDeck\Game\Player;
use WildDeck\Target\Exception\InvalidTargetException;
use WildDeck\Target\Exception\TargetRequiredException;

final class SelectedOpponentTarget implements TargetResolverInterface
{

    public function resolve(
        Game    $game,
        Player  $source,
        ?Player $selectedTarget,
    ): array
    {
        if ($selectedTarget === null) {
            throw new TargetRequiredException();
        }

        if ($selectedTarget === $source) {
            throw new InvalidTargetException();
        }

        return [$selectedTarget];
    }
}