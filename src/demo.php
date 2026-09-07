<?php

require __DIR__ . '/../vendor/autoload.php';

use WildDeck\Cards\Deck;
use WildDeck\Game\Game;
use WildDeck\Game\Player;

$deck = new Deck();
$deck->createDemoDeck();
$playerOne = new Player('Alice');
$playerTwo = new Player('Bob');

$game = new Game();

echo('Start game'  . PHP_EOL);
$game->createGame($playerOne, $playerTwo, $deck);

echo('Player One' . PHP_EOL);
showCards($playerOne);

echo('Player Two' . PHP_EOL);
showCards($playerTwo);

echo('FIGHT!' . PHP_EOL);
while (!$game->isEnded()){

    move($playerOne, $game);

    if ($game->isEnded()){
        break;
    }

    move($playerTwo, $game);

    if ($game->isEnded()){
        break;
    }
}


echo('End game, the winner is ' . $game->getWinner()?->getName() . PHP_EOL);


function move(Player $player, Game $game){
    $card = current($player->getHand()->get());
    echo $player->getName() . " -> ". $card->getName() . " |HP:  " . $player->getLifePoints() .  PHP_EOL;
    $game->playCard($player, $card);
    $game->endTurn($player);
}

function showCards(Player $player) {
    foreach ($player->getHand()->get() as $card)
    {
        echo $card->getName() . PHP_EOL;
    }
}

