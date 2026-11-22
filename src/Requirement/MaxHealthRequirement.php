<?php

declare(strict_types=1);

namespace WildDeck\Requirement;

use WildDeck\Combat\CombatInterface;
use WildDeck\Combatant\Player;

final readonly class MaxHealthRequirement implements RequirementInterface
{

    public function __construct(
        private int $maxHealth,
    ) {
    }

    public function check(
        CombatInterface $combat,
        Player          $player,
    ): RequirementResult {
        if ($player->getLifePoints() <= $this->maxHealth) {
            return new RequirementResult(true);
        }

        return new RequirementResult(
            false,
            'MAX_HEALTH_EXCEEDED',
        );
    }
}
