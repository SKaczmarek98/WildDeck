<?php

namespace WildDeck\Combat;

use WildDeck\Combatant\CombatantInterface;
use WildDeck\Combatant\Player;

interface CombatInterface
{
    public function drawCardFor(Player $player): void;

    /** @return CombatantInterface[] */
    public function getOpponentsOf(CombatantInterface $combatant): array;

}
