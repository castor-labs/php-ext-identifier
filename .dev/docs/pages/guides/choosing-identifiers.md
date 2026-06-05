---
description: Choose between UUID versions and ULIDs.
---
# Choosing identifiers

Identifier supports several identifier families because each one answers a slightly different question. The best choice is usually not the newest format or the shortest-looking string. It is the one whose ordering, privacy, and interoperability match the place where the value will live.

## Random public identifiers

UUID version 4 is the classic choice when you want an identifier that reveals nothing about when or where it was created. It is generated from random bytes and is widely understood by databases, APIs, and other languages.

```php
<?php

use Identifier\Uuid\Version4;

$token = Version4::generate();

echo $token->toString();
```

A version 4 UUID is a good fit for public tokens, correlation ids, and objects that do not need natural ordering. It is intentionally boring, and that is often a virtue. When a system already expects ordinary UUID strings, version 4 is the simplest way to integrate.

## Time-ordered UUIDs

UUID version 7 is usually the most pleasant UUID for new records. It keeps the standard UUID string shape while placing Unix millisecond time in the high bits. That means values created later tend to compare after values created earlier.

```php
<?php

use Identifier\Uuid\Version7;

$invoiceId = Version7::generate();

printf("%s was created around %d\n", $invoiceId, $invoiceId->getTimestamp());
```

That ordering can help database indexes because newly inserted rows are closer together than purely random UUIDs. The timestamp is not a replacement for your own `created_at` column, but it is useful when you need the identifier itself to carry rough creation order.

## ULIDs for compact sortable text

ULIDs also combine a millisecond timestamp with randomness, but their canonical text form is shorter and URL-friendly. The string is 26 characters long and sorts lexicographically by creation time.

```php
<?php

use Identifier\Ulid;

$eventId = Ulid::generate();

echo $eventId->toString();
echo $eventId->getTimestamp();
```

ULIDs are especially comfortable in logs, queues, and URLs where a dense sortable string is helpful. Within a single thread, the extension keeps ULID generation monotonic when multiple values are created in the same millisecond, so a burst of generated values remains ordered.

## Name-based UUIDs

Versions 3 and 5 are deterministic. They derive a UUID from a namespace and a name. The same namespace and name always produce the same value, which makes them useful for stable identifiers imported from another system.

```php
<?php

use Identifier\Uuid\Version5;

$namespace = hex2bin('6ba7b8119dad11d180b400c04fd430c8');
$id = Version5::generate($namespace, 'https://example.com/products/42');

echo $id->toString();
```

Use version 5 for new deterministic UUIDs unless you need version 3 for compatibility with older MD5-based data. The namespace argument is the 16-byte namespace UUID, so converting a known namespace from hexadecimal is a common way to make the input explicit.

## Legacy time-based UUIDs

Versions 1 and 6 use Gregorian-epoch time, a clock sequence, and node bytes. Version 6 rearranges the timestamp bits for better ordering while preserving the general version 1 model.

```php
<?php

use Identifier\Uuid\Version6;

$id = Version6::generate();

echo $id->getTimestamp();
echo bin2hex($id->getNode());
```

These versions are mainly useful when you need compatibility with systems that already speak in version 1 or version 6 UUIDs. For fresh application design, version 7 is usually easier to reason about because it uses Unix millisecond time.

The practical next step is learning how these values cross boundaries. Continue with [parsing, formatting, and storage](parsing-formatting-and-storage.html).
