---
description: Encode and decode binary values with the bundled codecs.
---
# Codecs

Identifier includes a small codec utility for binary-to-text conversion. UUID and ULID objects already know their canonical string formats, so you do not need codecs for normal identifier display. They become useful when you are working with raw bytes and need a specific alphabet for a token, a compact transport value, or a compatibility format.

The codec class lives in the `Encoding` namespace.

```php
<?php

use Encoding\Codec;

$codec = Codec::base64UrlSafe();
$encoded = $codec->encode(random_bytes(16));
$decoded = $codec->decode($encoded);
```

A codec is created with an alphabet and optional padding character. The named constructors cover the common alphabets, including hexadecimal, RFC 4648 base32, Crockford base32, Bitcoin base58, standard base64, URL-safe base64, and MIME base64.

## Encoding identifier bytes

Because every identifier can return its 16-byte representation, codecs compose naturally with UUIDs and ULIDs.

```php
<?php

use Encoding\Codec;
use Identifier\Uuid\Version7;

$id = Version7::generate();
$codec = Codec::base64UrlSafe();

$short = $codec->encode($id->toBytes());
$restored = Version7::fromBytes($codec->decode($short));

var_dump($id->equals($restored));
```

This does not change what the identifier is. It only changes how the same bytes are written. That distinction matters because other systems may not recognize a base64url-encoded UUID as a UUID unless you document the encoding in the API or schema.

## Choosing an alphabet

Hexadecimal is easy to read and debug, but it is not compact. Base64url is compact and friendly to URLs, but it is less familiar to humans. Crockford base32 is the alphabet used by ULID strings and avoids visually confusing characters. Base58 is popular in ecosystems that prefer copy-and-paste friendly tokens without punctuation.

```php
<?php

use Encoding\Codec;

$bytes = hex2bin('018f6d3f8c297a6db0a74a5f7b9c2d10');

echo Codec::hexadecimal()->encode($bytes);
echo Codec::base58Bitcoin()->encode($bytes);
```

There is no universally best alphabet. Pick the one that makes the surrounding protocol simplest, then keep that choice stable. Changing encodings later is a migration, even when the underlying 16 bytes stay the same.

## Custom codecs

When a protocol defines its own alphabet, construct a codec directly.

```php
<?php

use Encoding\Codec;

$alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
$codec = new Codec($alphabet);

$value = $codec->encode("hello");
```

The alphabet is part of the data contract. If you create a custom codec, give it a name in your application code rather than scattering the alphabet string through many files. Future readers should not have to rediscover which external system required that exact character order.

The usual identifier APIs are still the clearest choice for normal UUID and ULID work. Codecs are best treated as boundary tools for places where your bytes need a particular textual costume.
