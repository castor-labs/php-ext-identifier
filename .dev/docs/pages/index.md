---
description: Generate your first UUID and ULID with Identifier.
---
# Getting started

This page walks through the everyday shape of Identifier: generate an object, pass it around as a value, and decide at the boundary whether you want a string or bytes. The examples assume the extension is already built and enabled in PHP.

```php
<?php

use Identifier\Uuid\Version7;

$id = Version7::generate();

echo $id->toString();
echo $id->getVersion();
```

UUID version 7 is a friendly default for many server-side systems because it includes time information and therefore sorts well in many databases. The returned object is an instance of `Identifier\Uuid\Version7`, which also inherits the common behavior from `Identifier\Uuid` and `Identifier\Bit128`.

## Strings at the edges

Identifiers usually become strings when they leave your process. They appear in JSON payloads, logs, URLs, forms, and command output. For UUIDs, `toString()` returns the familiar dashed representation. For ULIDs, it returns the 26-character Crockford base32 representation.

```php
<?php

use Identifier\Ulid;
use Identifier\Uuid;

$uuid = Uuid::fromString('018f6d3f-8c29-7a6d-b0a7-4a5f7b9c2d10');
$ulid = Ulid::fromString('01HY5WZ31ABQ5Q0VZ4SN9K8M88');

echo $uuid;
echo $ulid;
```

The objects implement `Stringable`, so casting and interpolation call the canonical string conversion. Keeping the explicit `toString()` call in domain code can still make intent clearer, especially near serialization boundaries.

## Bytes for storage

Every identifier in this extension is backed by exactly 16 bytes. If your database column is binary, you can store those bytes directly and avoid the larger textual form.

```php
<?php

use Identifier\Uuid\Version7;

$id = Version7::generate();
$bytes = $id->toBytes();

$restored = Version7::fromBytes($bytes);

var_dump($id->equals($restored));
```

`toBytes()` and `getBytes()` return the same binary representation. The matching `fromBytes()` factory is inherited from `Identifier\Bit128`, so concrete UUID and ULID classes can be reconstructed from compact storage when the caller already knows the expected type.

## A small common model

The base class, `Identifier\Bit128`, exists because UUIDs and ULIDs share the same size. It provides binary conversion, hexadecimal conversion, equality, and lexicographic comparison. `Identifier\Uuid` adds UUID-specific parsing, version inspection, variant inspection, and the nil and max values. `Identifier\Ulid` adds ULID parsing plus access to the timestamp and randomness sections.

Most application code should depend on the concrete shape it expects. Use `Version7` when an order id must be a UUID v7, use `Ulid` when the public format is a ULID, and use `Bit128` only when you are writing lower-level code that truly does not care which 128-bit identifier it received.

The next guide explains how to make that choice deliberately: [choosing identifiers](guides/choosing-identifiers.html).
