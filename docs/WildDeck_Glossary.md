# WildDeck Glossary

This glossary defines the main domain terms used in WildDeck.

The goal is to keep naming consistent across the codebase and make the project understandable to anyone familiar with card games, RPGs, or turn-based combat.

## Core Combat Terms

### Combat

A single fight from start to resolution.

A Combat is responsible for the flow of one battle, including:

- turn order,
- card play,
- enemy actions,
- drawing and discarding cards,
- resolving effects,
- checking victory or defeat.

A Combat ends with a `CombatOutcome`.

Example:

```text
Player
  vs
Goblin
Orc
```

This entire fight is one Combat.

---

### Combatant

Any entity that can participate in Combat.

A Combatant can:

- have health,
- take damage,
- be healed,
- receive statuses,
- die.

Typical Combatants:

- `Player`
- `Enemy`

In code, generic combat logic should depend on `CombatantInterface` when it does not need to know the concrete type.

---

### Player

A Combatant controlled by the human player.

The Player has mechanics that generic Combatants do not necessarily have, such as:

- a hand of cards,
- mana,
- playing cards,
- drawing cards.

The Player may survive across multiple Combats as part of a future run or adventure.

---

### Enemy

A Combatant controlled by the game.

Enemies do not currently use cards.

During their turn they perform an automatic action, such as dealing damage to the Player.

Examples:

- Goblin
- Spider
- Orc
- Boss

More advanced enemy behavior may be introduced later.

---

### CombatOutcome

The result of a finished Combat.

Current outcomes:

```text
VICTORY
DEFEAT
```

`VICTORY` means the Player survived and all Enemies were defeated.

`DEFEAT` means the Player died.

There is no draw: if the Player dies, the run cannot continue.

---

## Card Terms

### Card

A playable game object used by the Player during Combat.

A Card can contain:

- cost,
- one or more Effects,
- Targets,
- Play Requirements.

Cards should describe what happens when they are played rather than contain the complete combat logic themselves.

---

### Deck

The Player's collection of cards.

The Deck represents the cards available to the Player and may later persist between Combats.

A Deck is different from temporary Combat card zones such as the hand, draw pile, and discard pile.

---

### Hand

Cards currently available for the Player to play.

The Hand exists as part of the current Combat state.

---

### Draw Pile

Cards that can still be drawn during the current Combat.

The Draw Pile is prepared from the Deck when Combat starts.

---

### Discard Pile

Cards that have already been played or discarded during the current Combat.

---

## Effect System

### Effect

An immediate change applied when a card or another game action resolves.

Examples:

- deal damage,
- heal,
- draw cards,
- apply a Status.

Examples in code:

```text
DamageEffect
HealEffect
DrawEffect
ApplyStatusEffect
```

An Effect performs an action now.

This is different from a Status, which remains attached to a Combatant and affects future gameplay.

---

### ResolvedEffect

An Effect together with its already resolved Targets.

Example:

```text
DamageEffect(5)
+
Goblin
```

becomes a ResolvedEffect before the effect is applied.

Resolving Effects before applying them helps prevent partially executed cards when one of their targets is invalid.

---

## Status System

### Status

A persistent state attached to a Combatant.

A Status can affect a Combatant over one or more turns.

Examples:

- Poison
- Burn
- Regeneration
- Stun
- Freeze

Current examples:

```text
PoisonStatus
BurnStatus
RegenerationStatus
```

A Status is different from an Effect:

```text
ApplyStatusEffect
    ↓
PoisonStatus
```

The Effect applies the Status.

The Status then remains on the Combatant and performs its own behavior over time.

---

## Targeting

### Target

An entity affected by an Effect.

Most Targets are Combatants.

Examples:

- self,
- selected opponent,
- all enemies,
- random enemy.

---

### TargetResolver

A rule responsible for resolving the actual Targets of an Effect.

Examples:

```text
SelfTarget
SelectedOpponentTarget
AllEnemiesTarget
RandomEnemyTarget
```

A TargetResolver may:

- use a target selected by the Player,
- validate that the selected target is legal,
- automatically find matching targets.

---

### Opponent

A Combatant on the opposing side of another Combatant.

`Opponent` describes a relationship, not a concrete entity type.

For example:

```text
Player's opponents -> Enemies
Enemy's opponent   -> Player
```

This is why `Enemy` and `Opponent` are not interchangeable terms.

---

## Card Play Rules

### PlayRequirement

A condition that must be satisfied before a Card can be played.

Examples:

- Player must be below a certain health threshold,
- Player must be at full health,
- another future gameplay condition must be satisfied.

A PlayRequirement answers:

> Can this Card be played right now?

It does not apply the Card's Effect.

---

## Turn Flow

Each Combatant has its own turn.

Example with one Player and two Enemies:

```text
Player
Enemy 1
Enemy 2
Player
Enemy 1
Enemy 2
...
```

A dead Combatant is skipped.

Player turns wait for input from the human player.

Enemy turns are resolved automatically by the Combat engine.

A simplified flow:

```text
Player turn
    ↓
Player plays cards
    ↓
Player ends turn
    ↓
Enemy 1 turn
    ↓
Enemy 1 action
    ↓
Enemy 2 turn
    ↓
Enemy 2 action
    ↓
Player turn
```

Statuses are processed as part of a Combatant's turn lifecycle.

---

## Naming Summary

| Term | Meaning |
|---|---|
| `Combat` | One complete fight |
| `Combatant` | Any entity participating in Combat |
| `Player` | Human-controlled Combatant |
| `Enemy` | Game-controlled Combatant |
| `CombatOutcome` | `VICTORY` or `DEFEAT` |
| `Card` | Playable card |
| `Deck` | Collection of cards owned by the Player |
| `Hand` | Cards currently playable |
| `DrawPile` | Cards waiting to be drawn |
| `DiscardPile` | Cards already used/discarded |
| `Effect` | Immediate gameplay change |
| `ResolvedEffect` | Effect with resolved Targets |
| `Status` | Persistent state attached to a Combatant |
| `Target` | Entity affected by an Effect |
| `TargetResolver` | Rule that resolves legal Targets |
| `Opponent` | Combatant on the opposing side |
| `PlayRequirement` | Condition required to play a Card |

## Naming Guidelines

Prefer domain language over technical implementation language.

For example, `CombatOutcome` is preferred over `GameResult` because it describes the actual domain concept.

Similarly, `PlayRequirement` is preferred over a generic `Requirement` because it communicates what the requirement is for.

When a method or class works with any participant of Combat, prefer `CombatantInterface`.

When behavior specifically belongs to the human-controlled Player, use `Player`.

When behavior specifically belongs to game-controlled opponents, use `Enemy`.

Avoid creating abstractions only for possible future use. Introduce new domain terms when they represent a real gameplay concept already needed by the game.
