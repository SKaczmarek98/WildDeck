<?php

namespace WildDeck\Requirement;

use WildDeck\Game\Game;
use WildDeck\Game\Player;

interface RequirementInterface
{
    public function check(
        Game   $game,
        Player $player,
    ): RequirementResult;
}
