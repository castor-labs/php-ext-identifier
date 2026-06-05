---
description: Move identifiers safely between strings, bytes, and PHP objects.
---
# Parsing, formatting, and storage

Identifiers spend most of their life crossing boundaries. A request gives you a string, a database gives you bytes, or a message bus asks for JSON. Identifier keeps those conversions explicit so your application can choose the representation that fits each boundary.

## Parsing UUID strings

When the UUID version is not known until runtime, parse through `Identifier\Uuid`. The parser validates the string and returns the concrete version class when the version is supported.

```php
<?php

use Identifier\Uuid;

$id = Uuid::fromString('018f6d3f-8c29-7a6d-b0a7-4a5f7b9c2d10');

if ($id->getVersion() === 7) {
    echo 'This is a UUID v7';
}
```

When a field is required to contain one specific version, parse with the concrete class instead. That makes the rule visible at the call site and lets invalid input fail early.

```php
<?php

use Identifier\Uuid\Version7;

$orderId = Version7::fromString('018f6d3f-8c29-7a6d-b0a7-4a5f7b9c2d10');
```

## Parsing ULID strings

ULIDs have their own canonical text form. Use `Identifier\Ulid::fromString()` when receiving one from an API route, form value, or queue message.

```php
<?php

use Identifier\Ulid;

$eventId = Ulid::fromString('01HY5WZ31ABQ5Q0VZ4SN9K8M88');

echo $eventId->getTimestamp();
```

The timestamp accessor returns the millisecond timestamp encoded in the ULID. That can be convenient for diagnostics, but your persistence model should still store business time explicitly when it matters.

## Binary storage

The most compact representation is the raw 16-byte value. This is often a good fit for `BINARY(16)` columns or binary protocol fields.

```php
<?php

use Identifier\Uuid\Version7;

$id = Version7::generate();
$payload = $id->toBytes();

$restored = Version7::fromBytes($payload);
```

The important discipline is to keep the expected type near the column or field definition. Bytes alone do not say whether they came from a UUID v7, a UUID v4, or a ULID. Your repository, mapper, or message schema should carry that meaning.

## Hexadecimal as a neutral bridge

`Identifier\Bit128` can also read and write hexadecimal. Hex is larger than binary, but it is convenient for debugging and for systems that want a dashless 32-character value.

```php
<?php

use Identifier\Bit128;

$id = Bit128::fromHex('018f6d3f8c297a6db0a74a5f7b9c2d10');

echo $id->toHex();
```

UUID strings with dashes are better for human-facing UUID values. Hex shines when you are inspecting raw bytes or building a generic 128-bit utility where UUID punctuation would be misleading.

## Equality and ordering

Identifier objects compare by their underlying bytes. `equals()` tells you whether two values are exactly the same, and `compare()` gives you lexicographic ordering.

```php
<?php

use Identifier\Ulid;

$first = Ulid::generate();
$second = Ulid::generate();

if ($first->compare($second) < 0) {
    echo 'The first ULID sorts before the second';
}
```

For time-ordered identifiers such as UUID v7 and ULID, byte ordering is useful because the timestamp lives at the beginning of the value. For random identifiers such as UUID v4, ordering is stable but not meaningful in a business sense.

Testing code that generates identifiers is easier when time and randomness are under your control. The next guide covers [deterministic generation](deterministic-generation.html).
