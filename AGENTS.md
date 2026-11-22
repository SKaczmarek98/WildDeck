# WildDeck

WildDeck is an educational PHP turn-based roguelike deckbuilder engine.

The project is intentionally framework-independent. Symfony may be added later for infrastructure such as API, persistence, authentication, or messaging, but the game engine itself should remain plain PHP.

## Project context

Before substantial gameplay or architectural changes, read:

- `docs/ROADMAP.md`

`docs/ROADMAP.md` is the source of truth for planned gameplay features, ideas, and current development direction.

Roadmap entries are not implementation requests by themselves. Do not implement a roadmap item unless the current task explicitly asks for it.

Keep `AGENTS.md` focused on durable project rules and architecture. Do not copy the full roadmap into this file.

## Current game model

The currently supported combat model is:

```text
1 Player
vs
1..N Enemies
```

PvP and multiplayer Player combat are not current requirements.

Do not recreate or preserve the old `Game` PvP architecture.

A combat consists of ordered Combatants. The current turn order is deterministic based on the order prepared by `Combat`; a future initiative system is planned but is not implemented yet.

Example:

```text
Player
Enemy 1
Enemy 2
Player
...
```

Each Combatant has its own turn.

Player turns wait for player actions.

Enemy turns resolve automatically.

Dead Combatants are skipped.

Combat ends with:

```text
CombatOutcome::VICTORY
CombatOutcome::DEFEAT
```

Player death always means defeat.

## Core domain concepts

### Combatant

`CombatantInterface` represents anything participating in combat.

`AbstractCombatant` contains shared implementation such as:

- health,
- damage,
- healing,
- statuses.

Current implementations are:

- `Player`
- `Enemy`

Generic combat mechanics should depend on `CombatantInterface`, not on `AbstractCombatant`.

Use concrete `Player` or `Enemy` types when behavior genuinely belongs only to that type.

### Player

Player-specific mechanics currently include:

- hand,
- mana,
- playing cards.

Only Player plays Cards at the moment.

### Enemy

Enemy turns are controlled by Combat.

Current Enemy behavior is intentionally simple: an Enemy has fixed attack damage and attacks the Player during its turn.

Do not introduce AI strategies, behavior trees, card-playing AI, or large Enemy abstraction hierarchies until multiple real Enemy behaviors require them.

When richer Enemy behavior is introduced, prefer reusing existing domain concepts such as Effects and targeting where they naturally fit instead of duplicating mechanics.

### Card

Cards are composed from behavior rather than subclassed by card type.

A Card contains:

- Effects,
- target rules,
- optional play Requirements,
- mana cost.

Do not introduce classes such as `DamageCard`, `HealCard`, etc. unless a real requirement makes composition insufficient.

### Effect

Effects perform immediate game state changes.

Examples:

- `DamageEffect`
- `HealEffect`
- `DrawEffect`
- `ApplyStatusEffect`

Effects that work on arbitrary combat participants should use `CombatantInterface`.

### Target

Target resolvers determine legal targets before Effects are executed.

Targets must be resolved before mutating combat state.

This prevents partially executed Cards when a later Effect has an invalid target.

### Requirement

Requirements determine whether a Player is allowed to play a Card.

Requirements are different from:

- mana cost,
- target validation,
- conditional Effect behavior.

Keep these concepts separate.

### Status

Statuses are persistent state attached to Combatants.

Current examples include:

- Burn,
- Poison,
- Regeneration.

Statuses currently process at the start of their owner's turn.

Do not build a generic event/event-bus system until actual game mechanics require lifecycle hooks other than turn start.

## Combat rules

Card play should be atomic:

1. validate the play,
2. resolve all Effect targets,
3. consume cost,
4. apply Effects,
5. move the Card to discard,
6. check Combat outcome.

Invalid targeting or unmet Requirements must not partially mutate game state.

Enemy turns currently resolve synchronously during Player `endTurn()`.

Combat automatically advances through Enemy turns until:

- Combat ends, or
- the next Player turn begins.

## Deck and combat zones

`Deck` represents the collection of Cards supplied to Combat.

Combat currently owns temporary card zones such as:

- draw pile,
- discard pile.

Player currently owns the hand.

The longer-term distinction between persistent Player Deck and temporary Combat card zones is not fully designed yet.

Do not introduce additional zone abstractions unless required by upcoming multi-combat/run mechanics.

## Architecture guidelines

Prefer simple domain objects and composition.

Do not introduce design patterns only because they may be useful later.

Introduce abstractions when at least one real use case requires them.

Avoid:

- giant generic game engines,
- event buses without real event-driven mechanics,
- unnecessary DTOs inside the domain,
- speculative interfaces,
- deep inheritance trees,
- framework dependencies in the core game engine.

Prefer:

- small explicit classes,
- typed PHP,
- constructor injection,
- domain-specific names,
- immutable value objects where appropriate,
- interfaces at meaningful dependency boundaries.

## Tests

Use PHPUnit.

Tests should primarily verify behavior through public APIs.

Prefer real domain objects over mocks.

Do not use reflection to inspect private state.

Do not preserve obsolete PvP behavior in tests.

When refactoring architecture:

- migrate tests that still represent valid game behavior,
- remove tests for abandoned behavior,
- add tests for new domain rules.

If a test exposes a production bug, report it rather than changing production behavior silently.

Run the complete relevant test suite after changes.

## Working style

Before making a substantial architectural change:

1. inspect the relevant existing classes,
2. read `docs/ROADMAP.md` when the task affects gameplay direction,
3. understand the current domain behavior,
4. prefer the smallest change that supports the requested feature,
5. avoid unrelated refactors,
6. run the full relevant test suite.

Do not implement speculative future systems unless explicitly requested.

When several designs are possible, prefer the simplest design that supports the current requirements and explain important tradeoffs.
