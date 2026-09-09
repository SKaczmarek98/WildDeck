<?php

declare(strict_types=1);

namespace WildDeck\Game\Exception;

use RuntimeException;

final class CardNotInHandException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Card is not in the player\'s hand.');
    }
}
