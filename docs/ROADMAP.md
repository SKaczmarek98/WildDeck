# WildDeck Roadmap

This is a living document for WildDeck gameplay direction, planned mechanics, and ideas.

It is intentionally not a detailed technical specification. Architecture should evolve from real gameplay requirements rather than from speculative future abstractions.

Legend:

- `[x]` implemented
- `[ ]` planned / not implemented
- **NEXT** — strong candidate for near-term work
- **LATER** — intentionally deferred
- **IDEA** — interesting, but not yet committed

## Current direction

WildDeck is a PvE turn-based roguelike deckbuilder.

Current combat model:

```text
1 Player
vs
1..N Enemies
```

The Player uses Cards.

Enemies currently resolve their own automatic turns and perform a simple fixed-damage attack.

PvP is not a current requirement.

## Current foundation

- [x] Cards
- [x] Deck
- [x] Card collections / hand
- [x] Draw pile and discard pile inside Combat
- [x] Mana / card cost
- [x] `RestoreManaEffect` — immediate mana recovery, capped at the Player's maximum
- [x] Effects
- [x] Target resolution
- [x] Play Requirements
- [x] Atomic card resolution before mutation
- [x] `CombatantInterface`
- [x] Player
- [x] Enemy
- [x] Multiple Enemies
- [x] Combat turn lifecycle
- [x] Automatic Enemy turns
- [x] Dead Combatants are skipped
- [x] `VICTORY` / `DEFEAT`
- [x] Burn
- [x] Poison
- [x] Regeneration
- [x] Basic Enemy fixed-damage attack
- [x] PHPUnit coverage for core Combat flow

## Combat mechanics

### Initiative — NEXT

Goal: Combat order should eventually be determined at Combat start instead of always following constructor order.

- [ ] Each Combatant rolls initiative when Combat begins.
- [ ] Combatants can have an intrinsic initiative modifier.
- [ ] Sort Combatants by final initiative result.
- [ ] Define deterministic tie-breaking.
- [ ] Keep random rolling testable and deterministic in tests.

Possible initiative formula:

```text
dice roll
+ Combatant initiative modifier
+ situational modifier
= final initiative
```

Situational modifiers should represent the encounter rather than permanently changing the Combatant.

Examples:

- ambush / surprise,
- enemies jumping out of bushes,
- temporary slow,
- temporary initiative bonus.

Do not design a large encounter modifier framework before one is actually needed.

### Armor

- [ ] Add Armor as a gameplay mechanic.
- [ ] Decide whether Armor is persistent points, a Status, or another Combatant property.
- [ ] Define when Armor is consumed or refreshed.
- [ ] Define its interaction with incoming damage.

Armor is expected to help expose the need for a real damage-resolution pipeline.

### Stun

- [ ] Add Stun.
- [ ] A stunned Combatant skips its action/turn.
- [ ] Define whether turn-start Statuses still process while stunned.
- [ ] Define stacking / duration behavior.

This should be implemented as a real turn-flow requirement, not by special-casing individual Enemies.

### Fatigue

Original idea:

> The next attack deals 50% damage.

- [ ] Add Fatigue or equivalent debuff.
- [ ] Decide exact rounding rules.
- [ ] Consume it after the relevant attack.
- [ ] Define whether it modifies only direct attacks or all outgoing damage.

### Damage Boost

Original idea:

> The next attack deals 50% more damage.

- [ ] Add a temporary outgoing damage boost.
- [ ] Consume it after the relevant attack.
- [ ] Define interaction/order with Fatigue and other modifiers.

Fatigue and Damage Boost will likely require damage to know more about its source than current `DamageEffect` does.

Do not build the modifier architecture before implementing a concrete mechanic that needs it.

### Mana / Stamina Boost

Original idea:

> Temporarily or immediately increase the resource available for playing Cards.

Possible variants:

- [x] restore additional mana immediately (`RestoreManaEffect`),
- [ ] increase mana for the current turn,
- [ ] temporarily increase maximum mana,
- [ ] persistent max-mana upgrade.

Immediate restoration is implemented and covered by Card-play tests, including recovery below and up to the maximum.

Do not implement all variants at once.

### Card-play limit debuff

Original idea:

> A debuff such as numbness limits the Player to e.g. 3 Cards per turn.

- [ ] Track Cards played during the current Player turn.
- [ ] Add a limit mechanic only when implementing the debuff.
- [ ] Reset turn-specific counters when a new Player turn begins.

Avoid introducing a generic action-point system unless more mechanics require one.

## Statuses

### Poison

- [x] Poison stacks.
- [x] Current Poison damage equals current stacks.
- [x] Poison decreases after processing each owner turn.
- [x] Reapplying Poison increases its stacks/duration.

Current model example:

```text
Poison 5
turn 1 -> 5 damage
turn 2 -> 4 damage
turn 3 -> 3 damage
turn 4 -> 2 damage
turn 5 -> 1 damage
```

### Burn

- [x] Deals fixed damage for a configured number of owner turns.
- [x] Stacking currently extends duration while keeping the original Burn damage.

### Regeneration

- [x] Heals at the start of the owner's turn.
- [x] Cannot heal above maximum health.

### Future Status ideas

- [ ] Stun
- [ ] Fatigue
- [ ] Damage Boost
- [ ] Card-play limit debuff
- [ ] Other future mechanics only when there is a concrete gameplay reason.

## Cards

### Temporary Cards

Original idea:

> Create a Card that exists only for the current turn.

- [ ] Add a mechanic that creates Temporary Cards.
- [ ] Decide whether they are added directly to Hand.
- [ ] Unused Temporary Cards should probably disappear at turn end.
- [ ] Decide whether played Temporary Cards go to discard or disappear immediately.
- [ ] Decide whether Temporary Cards can ever enter the persistent Deck.

This feature should be implemented after card-zone lifecycle rules are clearer.

## Enemies and AI

### Current

- [x] Enemy participates as a Combatant.
- [x] Enemy has health and Statuses.
- [x] Enemy can receive normal Effects.
- [x] Enemy performs a basic fixed-damage attack automatically.

### Enemy Actions — NEXT

The preferred direction is that Enemies have their own actions rather than pretending to be Players with Cards.

Initial examples:

```text
Goblin
-> Attack

Spider
-> Apply Poison

Pyromancer
-> Apply Burn

Healer
-> Heal an ally
```

- [ ] Introduce the first Enemy action that does something other than direct damage.
- [ ] Investigate reusing existing `Effect` and targeting concepts for Enemy actions.
- [ ] Do not create a large AI hierarchy before multiple real actions exist.
- [ ] Do not give Enemies Cards unless a future mechanic provides a strong reason.

### Later AI

- [ ] Enemy with multiple possible actions.
- [ ] Action-selection logic.
- [ ] Predictable patterns / alternating actions.
- [ ] Conditional decisions.
- [ ] Enemy intents visible to the Player.
- [ ] Boss behavior.

Start with deterministic/simple behavior before adding intelligent decision-making.

## Persistent Deck and Run — LATER

A major future step is moving from one isolated Combat to a roguelike Run.

### Persistent Deck

- [ ] Define Deck ownership outside Combat.
- [ ] Persistent Deck survives between Combats.
- [ ] Combat creates temporary draw/discard/hand zones from the persistent Deck.
- [ ] Define cleanup at the end of Combat.

### Run

Possible future model:

```text
Run
├── Player
├── Deck
├── Gold
├── Items / Trinkets
└── current Combat / encounter
```

- [ ] Multiple Combats in one Run.
- [ ] Player death ends the Run.
- [ ] Carry persistent state between Combats.

Do not introduce `Run` until multiple Combats and rewards actually need an owner.

## Combat rewards and drops — LATER

Original ideas:

- after Combat, choose one Card from several,
- add the selected Card to the Deck,
- receive Gold,
- receive item/trinket drops.

Planned mechanics:

- [ ] Generate Card reward choices after victory.
- [ ] Choose one Card from multiple candidates.
- [ ] Add chosen Card to the persistent Deck.
- [ ] Gold reward.
- [ ] Item / Trinket drops.
- [ ] Possibly different reward tables for normal, elite, and boss encounters.

Reward mechanics depend on a persistent Run/Deck model and should follow that work.

## Items / Trinkets — LATER

Items/Trinkets should provide persistent passive modifiers.

Original examples:

- Poison applied by the Player gets +1 stack.
- Poison deals +1 damage.
- Initiative bonus.
- Other persistent Combat modifiers.

Potential ideas:

- [ ] Item / Trinket ownership.
- [ ] Passive modifier system.
- [ ] Poison stack modifier.
- [ ] Poison damage modifier.
- [ ] Initiative modifier.
- [ ] Starting mana modifier.
- [ ] Other Card / Status / Combat modifiers.

Do not build a generic modifier/event system before concrete Items exist.

First implement the underlying mechanics that Items will modify.

## Ideas / not committed

This section is for ideas worth preserving without treating them as accepted requirements.

- **IDEA:** elite enemies.
- **IDEA:** bosses with deterministic or multi-phase patterns.
- **IDEA:** visible Enemy intent.
- **IDEA:** surprise / ambush encounters that modify initiative.
- **IDEA:** Card upgrades.
- **IDEA:** character classes / Player variants.
- **IDEA:** companions or summons as future Combatants.
- **IDEA:** special non-combat encounters.
- **IDEA:** shops / merchants.

Move an idea into a planned section only when we decide to actually build it.

## Suggested near-term order

This is guidance, not a hard commitment:

1. Initiative.
2. First richer Enemy Action, e.g. Poison or Burn.
3. Stun.
4. Armor / damage-resolution work.
5. Fatigue and Damage Boost.
6. Further Mana/Stamina modifiers (temporary/max mana boosts).
7. Card-play limit debuff.
8. Temporary Cards.
9. Persistent Deck / multiple Combats.
10. Rewards, Gold, drops.
11. Items / Trinkets.
12. More advanced Enemy AI and bosses.

Reorder this list when implementation teaches us something new.
