<?php

declare(strict_types=1);

namespace Identifier\Test;

use Exception;
use Encoding\Codec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CodecTest extends TestCase
{
    #[Test]
    public function standard_codecs_round_trip_strings(): void
    {
        $data = 'Hello World!';

        $cases = [
            Codec::base32Rfc4648(),
            Codec::base32Crockford(),
            Codec::base58Bitcoin(),
            Codec::base64Standard(),
            Codec::base64UrlSafe(),
            Codec::base64Mime(),
            new Codec('0123456789ABCDEF'),
            new Codec('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', '*'),
        ];

        foreach ($cases as $codec) {
            self::assertSame($data, $codec->decode($codec->encode($data)));
        }
    }

    #[Test]
    public function known_encodings_match_expected_values(): void
    {
        self::assertSame('SDFNRWG6ICXN5ZGYZBB', Codec::base32Rfc4648()->encode('Hello World!'));
        self::assertSame('J35DHP6Y82QDXS6RS11', Codec::base32Crockford()->encode('Hello World!'));
        self::assertSame('2NEpo7TZRRrLZSi2U', Codec::base58Bitcoin()->encode('Hello World!'));
        self::assertSame(base64_encode('Hello World!'), Codec::base64Standard()->encode('Hello World!'));
        self::assertSame('48656C6C6F20576F726C6421', (new Codec('0123456789ABCDEF'))->encode('Hello World!'));
    }

    #[Test]
    public function binary_data_round_trips(): void
    {
        $binaryData = str_repeat("\x00\x01\x02\x03", 4);
        $codec = Codec::base64Standard();
        $encoded = $codec->encode($binaryData);

        self::assertSame(16, strlen($binaryData));
        self::assertSame(20, strlen($encoded));
        self::assertSame($binaryData, $codec->decode($encoded));
    }



    /** @return iterable<string, array{string}> */
    public static function different_data_provider(): iterable
    {
        yield 'empty' => [''];
        yield 'single' => ['A'];
        yield 'short' => ['Hello'];
        yield 'long' => [str_repeat('X', 100)];
        yield 'binary' => ["\x00\x01\x02\x03"];
        yield 'unicode' => ['Special chars: àáâãäå'];
    }

    #[Test]
    #[DataProvider('different_data_provider')]
    public function base64_round_trips_different_data(string $data): void
    {
        $codec = Codec::base64Standard();
        self::assertSame($data, $codec->decode($codec->encode($data)));
    }
    #[Test]
    public function it_rejects_empty_alphabet(): void
    {
        $this->expectException(Exception::class);
        new Codec('');
    }

    #[Test]
    public function it_rejects_invalid_decode_characters(): void
    {
        $this->expectException(Exception::class);
        Codec::base64Standard()->decode('Invalid@Characters!');
    }

}
