<?php

declare(strict_types=1);

namespace WildDeck\Requirement;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

final readonly class MinHealthRequirement implements RequirementInterface
{

    public function __construct(
        private int $minHealth,
    )
    {
    }

    public function check(
        Game   $game,
        Player $player,
    ): RequirementResult
    {
        if ($player->getLifePoints() >= $this->minHealth) {
            return new RequirementResult(true);
        }

        return new RequirementResult(
            false,
            'MIN_HEALTH_EXCEEDED',
        );
    }
}
