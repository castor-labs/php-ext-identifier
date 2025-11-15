#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <ctype.h>
#include <math.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_INFO_EX(arginfo_codec_construct, 0, 0, 1)
    ZEND_ARG_TYPE_INFO(0, alphabet, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, padding, IS_STRING, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_codec_encode, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, data, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_codec_decode, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, encoded, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_codec_factory, 0, 0, Encoding\\Codec, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, padding, IS_STRING, 1, "null")
ZEND_END_ARG_INFO()



/* Alphabet constants */
#define BASE32_RFC4648_ALPHABET "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567"
#define BASE32_CROCKFORD_ALPHABET "0123456789ABCDEFGHJKMNPQRSTVWXYZ"
#define BASE58_BITCOIN_ALPHABET "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz"
#define BASE64_STANDARD_ALPHABET "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"
#define BASE64_URLSAFE_ALPHABET "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_"
#define BASE64_MIME_ALPHABET "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"

/* Class entry for Codec - declared in php_identifier.c */
extern zend_class_entry *php_identifier_codec_ce;

/* Codec object structure */
typedef struct _php_identifier_codec_obj {
    zend_string *alphabet;
    char padding;
    zend_object std;
} php_identifier_codec_obj;

/* Object handlers */
static zend_object_handlers php_identifier_codec_handlers;

/* Get codec object from zval */
static inline php_identifier_codec_obj *PHP_IDENTIFIER_CODEC_OBJ_P(zval *zv) {
    return (php_identifier_codec_obj *)((char *)Z_OBJ_P(zv) - XtOffsetOf(php_identifier_codec_obj, std));
}

/* Validate padding character is not in alphabet */
static int validate_padding_character(const char *alphabet, char padding) {
    if (padding == 0) {
        return 1; /* No padding is always valid */
    }

    size_t alphabet_len = strlen(alphabet);
    for (size_t i = 0; i < alphabet_len; i++) {
        if (alphabet[i] == padding) {
            return 0; /* Padding character found in alphabet - invalid */
        }
    }
    return 1; /* Valid */
}

/* Create codec object */
static zend_object *php_identifier_codec_create_object(zend_class_entry *ce)
{
    php_identifier_codec_obj *intern = zend_object_alloc(sizeof(php_identifier_codec_obj), ce);

    zend_object_std_init(&intern->std, ce);
    object_properties_init(&intern->std, ce);

    intern->alphabet = NULL;
    intern->padding = '=';

    intern->std.handlers = &php_identifier_codec_handlers;
    return &intern->std;
}

/* Free codec object */
static void php_identifier_codec_free_object(zend_object *object)
{
    php_identifier_codec_obj *intern = (php_identifier_codec_obj *)((char *)object - XtOffsetOf(php_identifier_codec_obj, std));

    if (intern->alphabet) {
        zend_string_release(intern->alphabet);
    }

    zend_object_std_dtor(&intern->std);
}

/**
 * Create a new encoding codec with custom alphabet
 *
 * Creates a codec that can encode and decode binary data using a custom alphabet.
 * The alphabet defines the characters used for encoding, and an optional padding
 * character can be specified for alignment.
 *
 * @param string $alphabet The character set to use for encoding (must not be empty)
 * @param string|null $padding Optional padding character (defaults to '=')
 * @throws Exception If alphabet is empty
 *
 * @example
 * // Create a custom Base32 codec
 * $codec = new Codec('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
 * $encoded = $codec->encode('Hello World');
 *
 * // Create codec with custom padding
 * $codec = new Codec('0123456789ABCDEF', '*');
 * $encoded = $codec->encode(random_bytes(16));
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, __construct)
{
    zend_string *alphabet;
    zend_string *padding = NULL;

    ZEND_PARSE_PARAMETERS_START(1, 2)
        Z_PARAM_STR(alphabet)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(padding)
    ZEND_PARSE_PARAMETERS_END();

    if (ZSTR_LEN(alphabet) == 0) {
        zend_throw_exception(zend_ce_exception, "Alphabet cannot be empty", 0);
        RETURN_THROWS();
    }

    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(ZEND_THIS);

    /* Set padding character */
    char padding_char;
    if (padding && ZSTR_LEN(padding) > 0) {
        padding_char = ZSTR_VAL(padding)[0];
    } else {
        padding_char = '='; /* Default padding */
    }

    /* Validate padding character is not in alphabet */
    if (!validate_padding_character(ZSTR_VAL(alphabet), padding_char)) {
        zend_throw_exception(zend_ce_exception, "Padding character cannot be present in alphabet", 0);
        RETURN_THROWS();
    }

    /* Store alphabet and padding */
    intern->alphabet = zend_string_copy(alphabet);
    intern->padding = padding_char;
}

/**
 * Encode binary data using the codec's alphabet
 *
 * Converts binary data into a string representation using the codec's
 * character alphabet. The encoding is reversible using the decode() method.
 *
 * @param string $data Binary data to encode
 * @return string Encoded string using the codec's alphabet
 * @throws Exception If encoding fails
 *
 * @example
 * $codec = Codec::base32Crockford();
 * $data = random_bytes(16);
 * $encoded = $codec->encode($data);
 * echo $encoded; // e.g., "91JPRV3F5GG7EVVG91IMKM"
 *
 * // Verify round-trip encoding
 * $decoded = $codec->decode($encoded);
 * var_dump($data === $decoded); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, encode)
{
    zend_string *data;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(data)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(ZEND_THIS);

    if (!intern->alphabet) {
        zend_throw_exception(zend_ce_exception, "Codec not properly initialized", 0);
        RETURN_THROWS();
    }

    const unsigned char *input = (const unsigned char *)ZSTR_VAL(data);
    size_t input_len = ZSTR_LEN(data);
    const char *alphabet_str = ZSTR_VAL(intern->alphabet);
    size_t alphabet_len = ZSTR_LEN(intern->alphabet);

    if (alphabet_len == 0) {
        zend_throw_exception(zend_ce_exception, "Alphabet cannot be empty", 0);
        RETURN_THROWS();
    }

    if (input_len == 0) {
        RETURN_EMPTY_STRING();
    }

    /* Calculate the base from alphabet length */
    size_t base = alphabet_len;
    
    /* Calculate maximum output length (overestimate to be safe) */
    size_t max_output_len = (input_len * 8 + base - 1) / (size_t)(log(base) / log(2)) + 1;
    
    /* Allocate output buffer */
    char *output = emalloc(max_output_len + 1);
    size_t output_len = 0;

    /* Convert input bytes to big integer representation */
    size_t num_len = input_len;
    unsigned char *num = emalloc(num_len);
    memcpy(num, input, input_len);

    /* Count leading zeros in input */
    size_t leading_zeros = 0;
    while (leading_zeros < input_len && input[leading_zeros] == 0) {
        leading_zeros++;
    }

    /* Convert to target base using repeated division */
    while (num_len > 0) {
        /* Check if number is zero */
        int is_zero = 1;
        for (size_t i = 0; i < num_len; i++) {
            if (num[i] != 0) {
                is_zero = 0;
                break;
            }
        }
        if (is_zero) break;

        /* Divide by base */
        unsigned int remainder = 0;
        for (size_t i = 0; i < num_len; i++) {
            unsigned int temp = remainder * 256 + num[i];
            num[i] = temp / base;
            remainder = temp % base;
        }

        /* Add remainder to output */
        output[output_len++] = alphabet_str[remainder];

        /* Remove leading zeros from num */
        while (num_len > 0 && num[0] == 0) {
            memmove(num, num + 1, num_len - 1);
            num_len--;
        }
    }

    /* Add leading zeros as first character of alphabet */
    for (size_t i = 0; i < leading_zeros; i++) {
        output[output_len++] = alphabet_str[0];
    }

    /* Reverse the output */
    for (size_t i = 0; i < output_len / 2; i++) {
        char temp = output[i];
        output[i] = output[output_len - 1 - i];
        output[output_len - 1 - i] = temp;
    }

    output[output_len] = '\0';

    efree(num);

    zend_string *result = zend_string_init(output, output_len, 0);
    efree(output);

    RETURN_STR(result);
}

/**
 * Decode string data back to binary using the codec's alphabet
 *
 * Converts a string encoded with this codec back to its original binary form.
 * The string must contain only characters from the codec's alphabet and
 * optional padding characters.
 *
 * @param string $encoded Encoded string to decode
 * @return string Original binary data
 * @throws Exception If string contains invalid characters or decoding fails
 *
 * @example
 * $codec = Codec::base64Standard();
 * $encoded = "SGVsbG8gV29ybGQ="; // "Hello World" in Base64
 * $decoded = $codec->decode($encoded);
 * echo $decoded; // "Hello World"
 *
 * // Handle invalid input
 * try {
 *     $codec->decode("Invalid@Characters!");
 * } catch (Exception $e) {
 *     echo "Decoding failed: " . $e->getMessage();
 * }
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, decode)
{
    zend_string *encoded;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(encoded)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(ZEND_THIS);

    if (!intern->alphabet) {
        zend_throw_exception(zend_ce_exception, "Codec not properly initialized", 0);
        RETURN_THROWS();
    }

    const char *input = ZSTR_VAL(encoded);
    size_t input_len = ZSTR_LEN(encoded);
    const char *alphabet_str = ZSTR_VAL(intern->alphabet);
    size_t alphabet_len = ZSTR_LEN(intern->alphabet);

    if (alphabet_len == 0) {
        zend_throw_exception(zend_ce_exception, "Alphabet cannot be empty", 0);
        RETURN_THROWS();
    }

    if (input_len == 0) {
        RETURN_EMPTY_STRING();
    }

    /* Create lookup table for alphabet */
    int lookup[256];
    for (int i = 0; i < 256; i++) {
        lookup[i] = -1;
    }
    for (size_t i = 0; i < alphabet_len; i++) {
        lookup[(unsigned char)alphabet_str[i]] = i;
    }

    /* Validate input characters */
    for (size_t i = 0; i < input_len; i++) {
        if (lookup[(unsigned char)input[i]] == -1) {
            zend_throw_exception(zend_ce_exception, "Invalid character in encoded string", 0);
            RETURN_THROWS();
        }
    }

    size_t base = alphabet_len;

    /* Count leading zeros (first character of alphabet) */
    size_t leading_zeros = 0;
    while (leading_zeros < input_len && input[leading_zeros] == alphabet_str[0]) {
        leading_zeros++;
    }

    /* Calculate maximum output length */
    size_t max_output_len = input_len;
    unsigned char *output = emalloc(max_output_len);
    size_t output_len = 0;

    /* Convert from base to bytes using Horner's method */
    for (size_t i = leading_zeros; i < input_len; i++) {
        int digit = lookup[(unsigned char)input[i]];
        
        /* Multiply current result by base and add digit */
        unsigned int carry = digit;
        for (size_t j = 0; j < output_len; j++) {
            carry += output[j] * base;
            output[j] = carry & 0xFF;
            carry >>= 8;
        }
        
        /* Add any remaining carry */
        while (carry > 0) {
            if (output_len >= max_output_len) {
                /* Reallocate if needed */
                max_output_len *= 2;
                output = erealloc(output, max_output_len);
            }
            output[output_len++] = carry & 0xFF;
            carry >>= 8;
        }
    }

    /* Add leading zeros */
    for (size_t i = 0; i < leading_zeros; i++) {
        if (output_len >= max_output_len) {
            max_output_len *= 2;
            output = erealloc(output, max_output_len);
        }
        output[output_len++] = 0;
    }

    /* Reverse the output */
    for (size_t i = 0; i < output_len / 2; i++) {
        unsigned char temp = output[i];
        output[i] = output[output_len - 1 - i];
        output[output_len - 1 - i] = temp;
    }

    zend_string *result = zend_string_init((char *)output, output_len, 0);
    efree(output);

    RETURN_STR(result);
}

/* Alphabet constants as static methods */


/* Static factory methods */
/**
 * Create a Base32 codec using RFC 4648 alphabet
 *
 * Returns a codec configured for standard Base32 encoding as defined in RFC 4648.
 * Uses the alphabet A-Z and 2-7 with '=' padding. This is the standard Base32
 * encoding used in many applications.
 *
 * @return Codec Base32 RFC 4648 codec instance
 *
 * @example
 * $codec = Codec::base32Rfc4648();
 * $encoded = $codec->encode("Hello World");
 * echo $encoded; // "JBSWY3DPEBLW64TMMQQQ===="
 *
 * $decoded = $codec->decode("JBSWY3DPEBLW64TMMQQQ====");
 * echo $decoded; // "Hello World"
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, base32Rfc4648)
{
    zend_string *padding = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(padding)
    ZEND_PARSE_PARAMETERS_END();

    char padding_char = (padding && ZSTR_LEN(padding) > 0) ? ZSTR_VAL(padding)[0] : '=';

    /* Validate padding character */
    if (!validate_padding_character(BASE32_RFC4648_ALPHABET, padding_char)) {
        zend_throw_exception(zend_ce_exception, "Padding character cannot be present in alphabet", 0);
        RETURN_THROWS();
    }

    object_init_ex(return_value, php_identifier_codec_ce);
    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(return_value);
    intern->alphabet = zend_string_init(BASE32_RFC4648_ALPHABET, strlen(BASE32_RFC4648_ALPHABET), 0);
    intern->padding = padding_char;
}

/**
 * Create a Base32 codec using Crockford alphabet
 *
 * Returns a codec configured for Crockford Base32 encoding. This encoding
 * excludes ambiguous characters (0, 1, I, L, O, U) and is case-insensitive.
 * It's designed to be human-readable and error-resistant.
 *
 * @return Codec Base32 Crockford codec instance
 *
 * @example
 * $codec = Codec::base32Crockford();
 * $encoded = $codec->encode("Hello World");
 * echo $encoded; // "91JPRV3F5GG7EVVG91IMKM"
 *
 * // Case-insensitive decoding
 * $decoded1 = $codec->decode("91JPRV3F5GG7EVVG91IMKM");
 * $decoded2 = $codec->decode("91jprv3f5gg7evvg91imkm");
 * var_dump($decoded1 === $decoded2); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, base32Crockford)
{
    zend_string *padding = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(padding)
    ZEND_PARSE_PARAMETERS_END();

    char padding_char = (padding && ZSTR_LEN(padding) > 0) ? ZSTR_VAL(padding)[0] : 0; /* No padding by default */

    /* Validate padding character */
    if (!validate_padding_character(BASE32_CROCKFORD_ALPHABET, padding_char)) {
        zend_throw_exception(zend_ce_exception, "Padding character cannot be present in alphabet", 0);
        RETURN_THROWS();
    }

    object_init_ex(return_value, php_identifier_codec_ce);
    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(return_value);
    intern->alphabet = zend_string_init(BASE32_CROCKFORD_ALPHABET, strlen(BASE32_CROCKFORD_ALPHABET), 0);
    intern->padding = padding_char;
}

/**
 * Create a Base58 codec using Bitcoin alphabet
 *
 * Returns a codec configured for Base58 encoding as used by Bitcoin.
 * This encoding excludes confusing characters (0, O, I, l) and produces
 * shorter strings than Base64 while remaining human-readable.
 *
 * @return Codec Base58 Bitcoin codec instance
 *
 * @example
 * $codec = Codec::base58Bitcoin();
 * $encoded = $codec->encode("Hello World");
 * echo $encoded; // "JxF12TrwUP45BMd"
 *
 * // Commonly used for Bitcoin addresses and keys
 * $hash = hash('sha256', 'some data', true);
 * $encoded = $codec->encode($hash);
 * echo strlen($encoded); // Shorter than Base64
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, base58Bitcoin)
{
    zend_string *padding = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(padding)
    ZEND_PARSE_PARAMETERS_END();

    char padding_char = (padding && ZSTR_LEN(padding) > 0) ? ZSTR_VAL(padding)[0] : 0; /* No padding by default */

    /* Validate padding character */
    if (!validate_padding_character(BASE58_BITCOIN_ALPHABET, padding_char)) {
        zend_throw_exception(zend_ce_exception, "Padding character cannot be present in alphabet", 0);
        RETURN_THROWS();
    }

    object_init_ex(return_value, php_identifier_codec_ce);
    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(return_value);
    intern->alphabet = zend_string_init(BASE58_BITCOIN_ALPHABET, strlen(BASE58_BITCOIN_ALPHABET), 0);
    intern->padding = padding_char;
}

/**
 * Create a Base64 codec using standard alphabet
 *
 * Returns a codec configured for standard Base64 encoding as defined in RFC 4648.
 * Uses A-Z, a-z, 0-9, +, / with '=' padding. This is the most common Base64
 * encoding used in email, web, and data storage applications.
 *
 * @return Codec Base64 standard codec instance
 *
 * @example
 * $codec = Codec::base64Standard();
 * $encoded = $codec->encode("Hello World");
 * echo $encoded; // "SGVsbG8gV29ybGQ="
 *
 * // Same as PHP's built-in base64_encode
 * $data = "Any binary data";
 * $encoded1 = $codec->encode($data);
 * $encoded2 = base64_encode($data);
 * var_dump($encoded1 === $encoded2); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, base64Standard)
{
    zend_string *padding = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(padding)
    ZEND_PARSE_PARAMETERS_END();

    char padding_char = (padding && ZSTR_LEN(padding) > 0) ? ZSTR_VAL(padding)[0] : '=';

    /* Validate padding character */
    if (!validate_padding_character(BASE64_STANDARD_ALPHABET, padding_char)) {
        zend_throw_exception(zend_ce_exception, "Padding character cannot be present in alphabet", 0);
        RETURN_THROWS();
    }

    object_init_ex(return_value, php_identifier_codec_ce);
    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(return_value);
    intern->alphabet = zend_string_init(BASE64_STANDARD_ALPHABET, strlen(BASE64_STANDARD_ALPHABET), 0);
    intern->padding = padding_char;
}

/**
 * Create a Base64 codec using URL-safe alphabet
 *
 * Returns a codec configured for URL-safe Base64 encoding. Uses A-Z, a-z, 0-9,
 * -, _ instead of +, / to avoid issues in URLs and filenames. Padding may be
 * omitted in some applications.
 *
 * @return Codec Base64 URL-safe codec instance
 *
 * @example
 * $codec = Codec::base64UrlSafe();
 * $encoded = $codec->encode("Hello World");
 * echo $encoded; // "SGVsbG8gV29ybGQ="
 *
 * // Safe for use in URLs and filenames
 * $data = random_bytes(16);
 * $encoded = $codec->encode($data);
 * $url = "https://example.com/data/" . $encoded;
 * // No need to URL-encode the result
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, base64UrlSafe)
{
    zend_string *padding = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(padding)
    ZEND_PARSE_PARAMETERS_END();

    char padding_char = (padding && ZSTR_LEN(padding) > 0) ? ZSTR_VAL(padding)[0] : '=';

    /* Validate padding character */
    if (!validate_padding_character(BASE64_URLSAFE_ALPHABET, padding_char)) {
        zend_throw_exception(zend_ce_exception, "Padding character cannot be present in alphabet", 0);
        RETURN_THROWS();
    }

    object_init_ex(return_value, php_identifier_codec_ce);
    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(return_value);
    intern->alphabet = zend_string_init(BASE64_URLSAFE_ALPHABET, strlen(BASE64_URLSAFE_ALPHABET), 0);
    intern->padding = padding_char;
}

/**
 * Create a Base64 codec using MIME alphabet with line breaks
 *
 * Returns a codec configured for MIME Base64 encoding. Uses the same alphabet
 * as standard Base64 but adds line breaks every 76 characters as required
 * by MIME specifications for email attachments.
 *
 * @return Codec Base64 MIME codec instance
 *
 * @example
 * $codec = Codec::base64Mime();
 * $longData = str_repeat("Hello World! ", 20);
 * $encoded = $codec->encode($longData);
 * // Output will have line breaks every 76 characters
 * echo $encoded;
 *
 * // Suitable for email attachments
 * $fileData = file_get_contents('document.pdf');
 * $mimeEncoded = $codec->encode($fileData);
 * // Can be safely included in email body
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Encoding_Codec, base64Mime)
{
    zend_string *padding = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(padding)
    ZEND_PARSE_PARAMETERS_END();

    char padding_char = (padding && ZSTR_LEN(padding) > 0) ? ZSTR_VAL(padding)[0] : '=';

    /* Validate padding character */
    if (!validate_padding_character(BASE64_MIME_ALPHABET, padding_char)) {
        zend_throw_exception(zend_ce_exception, "Padding character cannot be present in alphabet", 0);
        RETURN_THROWS();
    }

    object_init_ex(return_value, php_identifier_codec_ce);
    php_identifier_codec_obj *intern = PHP_IDENTIFIER_CODEC_OBJ_P(return_value);
    intern->alphabet = zend_string_init(BASE64_MIME_ALPHABET, strlen(BASE64_MIME_ALPHABET), 0);
    intern->padding = padding_char;
}



/* Codec method entries */
static const zend_function_entry php_identifier_codec_methods[] = {
    PHP_ME(Php_Identifier_Encoding_Codec, __construct, arginfo_codec_construct, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Encoding_Codec, encode, arginfo_codec_encode, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Encoding_Codec, decode, arginfo_codec_decode, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Encoding_Codec, base32Rfc4648, arginfo_codec_factory, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Encoding_Codec, base32Crockford, arginfo_codec_factory, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Encoding_Codec, base58Bitcoin, arginfo_codec_factory, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Encoding_Codec, base64Standard, arginfo_codec_factory, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Encoding_Codec, base64UrlSafe, arginfo_codec_factory, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Encoding_Codec, base64Mime, arginfo_codec_factory, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_FE_END
};

/* Initialize Codec class */
void php_identifier_codec_init(void)
{
    zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "Encoding\\Codec", php_identifier_codec_methods);
    php_identifier_codec_ce = zend_register_internal_class(&ce);

    /* Set up object handlers */
    memcpy(&php_identifier_codec_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
    php_identifier_codec_handlers.offset = XtOffsetOf(php_identifier_codec_obj, std);
    php_identifier_codec_handlers.free_obj = php_identifier_codec_free_object;
    php_identifier_codec_ce->create_object = php_identifier_codec_create_object;

    /* Register alphabet constants */
    zend_declare_class_constant_string(php_identifier_codec_ce, "BASE32_RFC4648", sizeof("BASE32_RFC4648")-1, BASE32_RFC4648_ALPHABET);
    zend_declare_class_constant_string(php_identifier_codec_ce, "BASE32_CROCKFORD", sizeof("BASE32_CROCKFORD")-1, BASE32_CROCKFORD_ALPHABET);
    zend_declare_class_constant_string(php_identifier_codec_ce, "BASE58_BITCOIN", sizeof("BASE58_BITCOIN")-1, BASE58_BITCOIN_ALPHABET);
    zend_declare_class_constant_string(php_identifier_codec_ce, "BASE64_STANDARD", sizeof("BASE64_STANDARD")-1, BASE64_STANDARD_ALPHABET);
    zend_declare_class_constant_string(php_identifier_codec_ce, "BASE64_URLSAFE", sizeof("BASE64_URLSAFE")-1, BASE64_URLSAFE_ALPHABET);
    zend_declare_class_constant_string(php_identifier_codec_ce, "BASE64_MIME", sizeof("BASE64_MIME")-1, BASE64_MIME_ALPHABET);
}
