<?php

declare(strict_types=1);

namespace WildDeck\Target;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combat\CombatInterface;
use WildDeck\Target\Exception\InvalidTargetException;
use WildDeck\Target\Exception\TargetRequiredException;

final class SelectedOpponentTarget implements TargetResolverInterface
{

    public function resolve(
        CombatInterface     $combat,
        CombatantInterface  $source,
        ?CombatantInterface $selectedTarget,
    ): array {
        if ($selectedTarget === null) {
            throw new TargetRequiredException();
        }

        if ($selectedTarget === $source) {
            throw new InvalidTargetException();
        }

        if (!in_array(
            $selectedTarget,
            $combat->getOpponentsOf($source),
            true
        )) {
            throw new InvalidTargetException();
        }

        return [$selectedTarget];
    }
}
