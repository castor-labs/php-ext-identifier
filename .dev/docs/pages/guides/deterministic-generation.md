---
description: Use generation state to make tests repeatable.
---
# Deterministic generation

Production identifiers should use real time and strong randomness. Tests often need the opposite. A good test wants the same value every time it runs, and it should not fail because the clock moved between two assertions. Identifier handles this with generation state.

The `Identifier\State` interface supplies the timestamp and random bytes used by generators. When you do not pass a state, the extension uses the system state. When you want repeatable output, create a fixed state and pass it to the generator.

```php
<?php

use Identifier\State\Fixed;
use Identifier\Uuid\Version7;

$state = Fixed::create(1_700_000_000_000, 42);

$id = Version7::generate($state);

echo $id->toString();
```

The first argument is Unix time in milliseconds. The second argument is a seed for deterministic random bytes. Together they make the generated identifier predictable without changing your production code path.

## Advancing time in a test

A fixed state does not have to stay frozen. You can move it forward when a scenario needs multiple moments in time.

```php
<?php

use Identifier\State\Fixed;
use Identifier\Ulid;

$state = Fixed::create(1_700_000_000_000, 123);

$created = Ulid::generate($state);
$state->advanceTimeSeconds(5);
$processed = Ulid::generate($state);

var_dump($created->compare($processed) < 0);
```

This style keeps time as part of the test story. Instead of sleeping or mocking global functions, the test says that five seconds passed and then asks the generator for another value.

## Sharing the same idea across UUID versions

The same state object can be used with generators that need randomness, Unix millisecond time, or Gregorian-epoch time. That includes UUID versions 1, 4, 6, and 7, plus ULIDs.

```php
<?php

use Identifier\State\Fixed;
use Identifier\Uuid\Version4;
use Identifier\Uuid\Version7;

$state = Fixed::create(1_700_000_000_000, 99);

$randomLooking = Version4::generate($state);
$timeOrdered = Version7::generate($state);
```

The names still describe the UUID version correctly. A version 4 UUID generated with fixed state is still a version 4 UUID; it is just using deterministic bytes so your test can assert against it safely.

## Returning to system generation

Most application code does not need to create a system state manually. Calling `generate()` without arguments uses real system behavior.

```php
<?php

use Identifier\Uuid\Version7;

$id = Version7::generate();
```

If you are building an abstraction around identifier generation, accepting an optional `Identifier\State` is often enough. Production code can omit it, while tests can pass `Fixed` and avoid global mocking.
