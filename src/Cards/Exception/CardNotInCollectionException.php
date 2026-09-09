<?php

declare(strict_types=1);

namespace WildDeck\Cards\Exception;

use RuntimeException;

final class CardNotInCollectionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Card is not in collection.');
    }
}
