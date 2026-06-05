---
description: Fast UUID and ULID generation for PHP applications.
---
# Identifier extension

Identifier is a native PHP extension for applications that create, parse, compare, and store 128-bit identifiers. It gives you UUIDs, ULIDs, and a small binary foundation without asking your application code to carry the cost of doing that work in userland PHP.

The extension is useful anywhere identifiers sit on a hot path. A web API may need a new id for every request, an event store may generate millions of ordered ids, and a database layer may prefer compact 16-byte values instead of long strings. Identifier keeps those use cases close to PHP while doing the heavy lifting in native code.

```php
<?php

use Identifier\Ulid;
use Identifier\Uuid\Version4;
use Identifier\Uuid\Version7;

$orderId = Version7::generate();
$publicToken = Version4::generate();
$eventId = Ulid::generate();

echo $orderId->toString();
echo $publicToken->toString();
echo $eventId->toString();
```

A generated object is not just a string wrapper. Every identifier keeps its 16-byte value, can be compared with another identifier, can be converted to a canonical string representation, and can be round-tripped through binary storage.

If you are new to the extension, start with [getting started](docs/index.html). Once the basics are comfortable, the guides explain how to choose an identifier shape, how to parse and store values safely, how deterministic generation works in tests, and how the bundled codecs can help when you need another textual representation.
