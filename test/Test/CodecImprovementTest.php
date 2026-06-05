<?php

declare(strict_types=1);

namespace Identifier\Test;

use Exception;
use Encoding\Codec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CodecImprovementTest extends TestCase
{
    #[Test]
    public function binary_codec_encodes_and_decodes_known_value(): void
    {
        $codec = Codec::binary();
        $data = "\x48\x69";

        $encoded = $codec->encode($data);

        self::assertSame('100100001101001', $encoded);
        self::assertSame($data, $codec->decode($encoded));
    }

    #[Test]
    public function hexadecimal_codec_encodes_and_decodes_known_value(): void
    {
        $codec = Codec::hexadecimal();
        $encoded = $codec->encode('Hello');

        self::assertSame('48656C6C6F', $encoded);
        self::assertSame('Hello', $codec->decode($encoded));
    }

    #[Test]
    public function alphabet_constants_are_exposed(): void
    {
        self::assertSame('01', Codec::BINARY);
        self::assertSame('0123456789ABCDEF', Codec::HEXADECIMAL);
        self::assertSame(2, strlen(Codec::BINARY));
        self::assertSame(16, strlen(Codec::HEXADECIMAL));
    }



    #[Test]
    public function valid_alphabet_lengths_are_accepted(): void
    {
        self::assertInstanceOf(Codec::class, new Codec('AB'));
        self::assertInstanceOf(Codec::class, new Codec('ABCD'));
    }



    #[Test]
    public function no_duplicate_alphabet_is_accepted(): void
    {
        self::assertInstanceOf(Codec::class, new Codec('ABCDEFGH'));
    }


    /** @return iterable<string, array{string, string}> */
    public static function binary_cases(): iterable
    {
        yield 'zero' => ["\x00", '0'];
        yield 'one' => ["\x01", '1'];
        yield 'ff' => ["\xFF", '11111111'];
        yield 'one-zero' => ["\x01\x00", '100000000'];
    }

    #[Test]
    #[DataProvider('binary_cases')]
    public function binary_codec_handles_various_data(string $input, string $expected): void
    {
        self::assertSame($expected, Codec::binary()->encode($input));
    }

    /** @return iterable<string, array{string, string}> */
    public static function hexadecimal_cases(): iterable
    {
        yield 'zero' => ["\x00", '0'];
        yield '0f' => ["\x0F", 'F'];
        yield 'ff' => ["\xFF", 'FF'];
        yield 'deadbeef' => ["\xDE\xAD\xBE\xEF", 'DEADBEEF'];
    }

    #[Test]
    #[DataProvider('hexadecimal_cases')]
    public function hexadecimal_codec_handles_various_data(string $input, string $expected): void
    {
        self::assertSame($expected, Codec::hexadecimal()->encode($input));
    }

    #[Test]
    public function binary_and_hexadecimal_round_trip_random_bytes(): void
    {
        $data = random_bytes(16);

        self::assertSame($data, Codec::binary()->decode(Codec::binary()->encode($data)));
        self::assertSame($data, Codec::hexadecimal()->decode(Codec::hexadecimal()->encode($data)));
    }
    #[Test]
    public function alphabet_length_must_be_multiple_of_two(): void
    {
        $this->expectException(Exception::class);
        new Codec('ABC');
    }

    #[Test]
    public function another_odd_alphabet_length_is_rejected(): void
    {
        $this->expectException(Exception::class);
        new Codec('ABCDE');
    }

    #[Test]
    public function duplicate_alphabet_characters_are_rejected(): void
    {
        $this->expectException(Exception::class);
        new Codec('AABB');
    }

    #[Test]
    public function duplicate_numeric_alphabet_characters_are_rejected(): void
    {
        $this->expectException(Exception::class);
        new Codec('0123456789012345');
    }

    #[Test]
    public function length_validation_happens_before_duplicate_validation(): void
    {
        $this->expectExceptionMessage('Alphabet length must be a multiple of 2');
        new Codec('AAA');
    }

}
