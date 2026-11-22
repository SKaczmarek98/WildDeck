<?php

declare(strict_types=1);

namespace WildDeck\Game;

enum CombatResult
{
    case PLAYER_WIN;
    case ENEMY_WIN;
}