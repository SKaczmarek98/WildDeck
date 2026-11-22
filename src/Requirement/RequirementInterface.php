<?php

namespace WildDeck\Requirement;

use WildDeck\Combat\CombatInterface;
use WildDeck\Combatant\Player;

interface RequirementInterface
{
    public function check(
        CombatInterface $combat,
        Player          $player,
    ): RequirementResult;
}
