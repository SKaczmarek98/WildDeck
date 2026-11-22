<?php

namespace WildDeck\Requirement;

use WildDeck\Combatant\Player;
use WildDeck\Game\Game;

interface RequirementInterface
{
    public function check(
        Game   $game,
        Player $player,
    ): RequirementResult;
}
