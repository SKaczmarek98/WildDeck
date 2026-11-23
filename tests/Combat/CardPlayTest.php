<?php

declare(strict_types=1);

namespace WildDeck\Tests\Combat;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WildDeck\Cards\Card;
use WildDeck\Cards\CardEffect;
use WildDeck\Cards\Deck;
use WildDeck\Combat\Combat;
use WildDeck\Combat\Exception\CardNotInHandException;
use WildDeck\Combat\Exception\CombatAlreadyEndedException;
use WildDeck\Combat\Exception\NoManaToPlayCardException;
use WildDeck\Combat\Exception\RequirementNotSatisfiedException;
use WildDeck\Combatant\Enemy;
use WildDeck\Combatant\Player;
use WildDeck\Effect\DamageEffect;
use WildDeck\Effect\DrawEffect;
use WildDeck\Effect\HealEffect;
use WildDeck\Effect\RestoreManaEffect;
use WildDeck\Requirement\MaxHealthRequirement;
use WildDeck\Target\Exception\InvalidTargetException;
use WildDeck\Target\Exception\TargetRequiredException;
use WildDeck\Target\SelectedOpponentTarget;
use WildDeck\Target\SelfTargetResolver;
use WildDeck\Tests\TestHelper;

final class CardPlayTest extends TestCase
{
    public function testDrainHitsOnlyTheSelectedEnemyHealsThePlayerAndSpendsMana(): void
    {
        $player = new Player('Hero');
        $first = new Enemy('First', 20, 0);
        $second = new Enemy('Second', 20, 0);
        $card = new Card('Drain', [
            new CardEffect(new DamageEffect(3), new SelectedOpponentTarget()),
            new CardEffect(new HealEffect(3), new SelfTargetResolver()),
        ], cost: 1);
        $combat = new Combat($player, [$first, $second], TestHelper::createDeck($card));
        $player->takeDamage(10);

        $combat->playCard($player, $card, $second);

        self::assertSame(20, $first->getLifePoints());
        self::assertSame(17, $second->getLifePoints());
        self::assertSame(13, $player->getLifePoints());
        self::assertSame(2, $player->getManaPoints());
        self::assertFalse($player->getHand()->contains($card));

        $combat->endTurn($player);

        self::assertFalse($player->getHand()->contains($card));
        $this->expectException(CardNotInHandException::class);
        $combat->playCard($player, $card, $second);
    }

    public function testHealEffectCanHealAnEnemyUpToItsMaximumHealth(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 12, 0);
        $card = new Card('Heal opponent', [
            new CardEffect(new HealEffect(5), new SelectedOpponentTarget()),
        ]);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));
        $enemy->takeDamage(3);

        $combat->playCard($player, $card, $enemy);

        self::assertSame(12, $enemy->getLifePoints());
    }

    public function testDrawCardAddsTwoCardsToThePlayersHand(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $draw = new Card('Draw', [
            new CardEffect(new DrawEffect(2), new SelfTargetResolver()),
        ]);
        $deck = new Deck([
            new Card('Filler 1', []),
            new Card('Filler 2', []),
            new Card('Filler 3', []),
            new Card('Filler 4', []),
            new Card('Filler 5', []),
        ]);
        $combat = new Combat($player, [$enemy], $deck);
        $player->getHand()->add($draw);

        $combat->playCard($player, $draw);

        self::assertSame(5, $player->getHand()->count());
        self::assertFalse($player->getHand()->contains($draw));
    }

    #[DataProvider('meditationManaCosts')]
    public function testMeditationRestoresTwoManaUpToTheMaximum(int $manaCost, int $expectedMana): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $fireball = TestHelper::createFireballCard(cost: $manaCost);
        $meditation = new Card('Meditation', [
            new CardEffect(new RestoreManaEffect(2), new SelfTargetResolver()),
        ]);
        $combat = new Combat($player, [$enemy], new Deck([$fireball, $meditation]));

        self::assertSame(3, $player->getManaPoints());
        $combat->playCard($player, $fireball, $enemy);
        self::assertSame(3 - $manaCost, $player->getManaPoints());

        $combat->playCard($player, $meditation);

        self::assertSame($expectedMana, $player->getManaPoints());
    }

    public static function meditationManaCosts(): array
    {
        return [
            'restores two mana without reaching the maximum' => [3, 2],
            'restores two mana to reach the maximum' => [2, 3],
            'caps the restored mana at the maximum' => [1, 3],
        ];
    }

    public function testPlayerCannotPlayCardWithoutEnoughMana(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $card = TestHelper::createFireballCard(cost: 4);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));

        $this->expectException(NoManaToPlayCardException::class);
        $combat->playCard($player, $card, $enemy);
    }

    public function testCardRequiresLowHealthBeforeItCanBePlayed(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $card = new Card('Low health fireball', [
            new CardEffect(new DamageEffect(5), new SelectedOpponentTarget()),
        ], cost: 1, requirements: [new MaxHealthRequirement(10)]);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));
        $handBefore = $player->getHand()->getAll();

        try {
            $combat->playCard($player, $card, $enemy);
            self::fail('An unmet requirement should reject the card.');
        } catch (RequirementNotSatisfiedException $exception) {
            self::assertSame('MAX_HEALTH_EXCEEDED', $exception->getMessage());
        }

        self::assertSame(3, $player->getManaPoints());
        self::assertSame(20, $enemy->getLifePoints());
        self::assertSame($handBefore, $player->getHand()->getAll());

        $player->takeDamage(10);
        $combat->playCard($player, $card, $enemy);

        self::assertSame(15, $enemy->getLifePoints());
        self::assertSame(2, $player->getManaPoints());
    }

    public function testInvalidTargetLeavesManaHandAndHealthUnchanged(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $outsider = new Enemy('Outsider', 20, 0);
        $card = new Card('Drain', [
            new CardEffect(new HealEffect(3), new SelfTargetResolver()),
            new CardEffect(new DamageEffect(3), new SelectedOpponentTarget()),
        ], cost: 1);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));
        $player->takeDamage(10);
        $handBefore = $player->getHand()->getAll();

        try {
            $combat->playCard($player, $card, $outsider);
            self::fail('A target outside combat should reject the card.');
        } catch (InvalidTargetException) {
            self::assertSame(3, $player->getManaPoints());
            self::assertSame($handBefore, $player->getHand()->getAll());
            self::assertSame(10, $player->getLifePoints());
            self::assertSame(20, $enemy->getLifePoints());
            self::assertSame(20, $outsider->getLifePoints());
        }
    }

    public function testOmittingARequiredTargetPreservesManaAndHand(): void
    {
        $player = new Player('Hero');
        $enemy = new Enemy('Enemy', 20, 0);
        $card = TestHelper::createFireballCard(cost: 1);
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));

        $this->expectException(TargetRequiredException::class);
        try {
            $combat->playCard($player, $card);
        } finally {
            self::assertSame(3, $player->getManaPoints());
            self::assertTrue($player->getHand()->contains($card));
        }
    }

    public function testSelectingADeadEnemyPreservesManaAndHand(): void
    {
        $player = new Player('Hero');
        $dead = new Enemy('Dead', 20, 0);
        $living = new Enemy('Living', 20, 0);
        $card = TestHelper::createFireballCard(cost: 1);
        $combat = new Combat($player, [$dead, $living], TestHelper::createDeck($card));
        $dead->takeDamage(20);

        $this->expectException(InvalidTargetException::class);
        try {
            $combat->playCard($player, $card, $dead);
        } finally {
            self::assertSame(3, $player->getManaPoints());
            self::assertTrue($player->getHand()->contains($card));
        }
    }

    public function testPlayerCannotPlayCardAfterCombatEnds(): void
    {
        $player = new Player('Hero');
        $player->takeDamage(18);
        $enemy = new Enemy('Enemy', 20, 2);
        $card = TestHelper::createFireballCard();
        $combat = new Combat($player, [$enemy], TestHelper::createDeck($card));

        $combat->endTurn($player);

        $this->expectException(CombatAlreadyEndedException::class);
        $combat->playCard($player, $card, $enemy);
    }
}
