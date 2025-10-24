#include "php.h"
#include "php_identifier.h"
#include "zend_exceptions.h"
#include "zend_interfaces.h"
#include <string.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_ulid_generate, 0, 0, Php\\Identifier\\Ulid, 0)
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, context, Php\\Identifier\\Context, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_ulid_toString, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_ulid_fromString, 0, 1, Php\\Identifier\\Ulid, 0)
    ZEND_ARG_TYPE_INFO(0, ulid, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_ulid_fromHex, 0, 1, Php\\Identifier\\Ulid, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_ulid_fromBytes, 0, 1, Php\\Identifier\\Ulid, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_ulid_getTimestamp, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_ulid_getRandomness, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

/* ULID constants */
#define ULID_TIMESTAMP_BYTES 6
#define ULID_RANDOMNESS_BYTES 10
#define ULID_TOTAL_BYTES 16
#define ULID_STRING_LENGTH 26

/* Static variables for monotonic generation */
static uint64_t last_timestamp = 0;
static unsigned char last_randomness[ULID_RANDOMNESS_BYTES];
static int randomness_initialized = 0;

/* Forward declarations */
extern zend_class_entry *php_identifier_codec_ce;

/* Helper function to call Codec::base32Crockford() */
static zend_string* get_crockford_alphabet(void)
{
    zval result;
    zend_call_method(NULL, php_identifier_codec_ce, NULL, "base32Crockford", 15, &result, 0, NULL, NULL);

    if (Z_TYPE(result) == IS_STRING) {
        return Z_STR(result);
    }

    /* Fallback to hardcoded alphabet */
    return zend_string_init("0123456789ABCDEFGHJKMNPQRSTVWXYZ", 32, 0);
}

/* Helper function to call Codec::encode() */
static zend_string* codec_encode(const unsigned char *data, size_t data_len, zend_string *alphabet)
{
    zval result;
    zval data_zval, alphabet_zval;

    ZVAL_STRINGL(&data_zval, (char*)data, data_len);
    ZVAL_STR(&alphabet_zval, alphabet);

    zval params[2] = { data_zval, alphabet_zval };

    zend_call_method(NULL, php_identifier_codec_ce, NULL, "encode", 6, &result, 2, &params[0], &params[1]);

    zval_dtor(&data_zval);

    if (Z_TYPE(result) == IS_STRING) {
        return Z_STR(result);
    }

    return zend_string_init("", 0, 0);
}

/* Helper function to call Codec::decode() */
static zend_string* codec_decode(zend_string *encoded, zend_string *alphabet)
{
    zval result;
    zval encoded_zval, alphabet_zval;

    ZVAL_STR(&encoded_zval, encoded);
    ZVAL_STR(&alphabet_zval, alphabet);

    zval params[2] = { encoded_zval, alphabet_zval };

    zend_call_method(NULL, php_identifier_codec_ce, NULL, "decode", 6, &result, 2, &params[0], &params[1]);

    if (Z_TYPE(result) == IS_STRING) {
        return Z_STR(result);
    }

    return zend_string_init("", 0, 0);
}

/* Increment randomness for monotonic generation */
static void increment_randomness(unsigned char *randomness)
{
    for (int i = ULID_RANDOMNESS_BYTES - 1; i >= 0; i--) {
        if (randomness[i] < 255) {
            randomness[i]++;
            break;
        }
        randomness[i] = 0;
    }
}

/* ULID generation method */

/**
 * Generate a new ULID (Universally Unique Lexicographically Sortable Identifier)
 *
 * Creates a new ULID with a timestamp component and random component.
 * ULIDs are lexicographically sortable and encode a timestamp, making them
 * ideal for use as database primary keys and distributed system identifiers.
 *
 * @param Context|null $context Optional context for controlling time and randomness
 * @return Ulid A new ULID instance
 * @throws Exception If timestamp or random generation fails
 *
 * @example
 * // Generate with current timestamp
 * $ulid = Ulid::generate();
 * echo $ulid->toString(); // e.g., "01ARZ3NDEKTSV4RRFFQ69G5FAV"
 *
 * // Generate with fixed context for testing
 * $context = new FixedContext();
 * $ulid = Ulid::generate($context);
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Ulid, generate)
{
    zval *context = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_OBJECT_OF_CLASS_OR_NULL(context, php_identifier_context_ce)
    ZEND_PARSE_PARAMETERS_END();

    /* Get timestamp from context or system */
    uint64_t current_timestamp;
    if (context) {
        /* Call getTimestampMs on context */
        zval ts_result;
        zend_call_method(Z_OBJ_P(context), Z_OBJCE_P(context), NULL, "gettimestampms", 14, &ts_result, 0, NULL, NULL);
        if (Z_TYPE(ts_result) == IS_LONG) {
            current_timestamp = Z_LVAL(ts_result);
        } else {
            current_timestamp = php_identifier_get_timestamp_ms();
        }
        zval_dtor(&ts_result);
    } else {
        current_timestamp = php_identifier_get_timestamp_ms();
    }

    /* Generate randomness */
    unsigned char randomness[ULID_RANDOMNESS_BYTES];

    if (current_timestamp == last_timestamp && randomness_initialized) {
        /* Same timestamp - increment randomness for monotonic ordering */
        memcpy(randomness, last_randomness, ULID_RANDOMNESS_BYTES);
        increment_randomness(randomness);
    } else {
        /* New timestamp - generate fresh randomness */
        if (context) {
            /* Call getRandomBytes on context */
            zval rand_result;
            zval rand_param;
            ZVAL_LONG(&rand_param, ULID_RANDOMNESS_BYTES);
            zend_call_method(Z_OBJ_P(context), Z_OBJCE_P(context), NULL, "getrandombytes", 14, &rand_result, 1, &rand_param, NULL);

            if (Z_TYPE(rand_result) == IS_STRING && Z_STRLEN(rand_result) == ULID_RANDOMNESS_BYTES) {
                memcpy(randomness, Z_STRVAL(rand_result), ULID_RANDOMNESS_BYTES);
            } else {
                php_identifier_generate_random_bytes(randomness, ULID_RANDOMNESS_BYTES);
            }
            zval_dtor(&rand_result);
        } else {
            php_identifier_generate_random_bytes(randomness, ULID_RANDOMNESS_BYTES);
        }
    }

    /* Update static state for monotonic generation */
    last_timestamp = current_timestamp;
    memcpy(last_randomness, randomness, ULID_RANDOMNESS_BYTES);
    randomness_initialized = 1;

    /* Create ULID bytes: 6 bytes timestamp + 10 bytes randomness */
    unsigned char ulid_bytes[ULID_TOTAL_BYTES];

    /* Pack timestamp as big-endian 48-bit value */
    ulid_bytes[0] = (current_timestamp >> 40) & 0xFF;
    ulid_bytes[1] = (current_timestamp >> 32) & 0xFF;
    ulid_bytes[2] = (current_timestamp >> 24) & 0xFF;
    ulid_bytes[3] = (current_timestamp >> 16) & 0xFF;
    ulid_bytes[4] = (current_timestamp >> 8) & 0xFF;
    ulid_bytes[5] = current_timestamp & 0xFF;

    /* Copy randomness */
    memcpy(ulid_bytes + ULID_TIMESTAMP_BYTES, randomness, ULID_RANDOMNESS_BYTES);

    /* Create ULID object */
    object_init_ex(return_value, php_identifier_ulid_ce);

    /* Set the bytes in the Bit128 parent */
    zval bytes_zval;
    ZVAL_STRINGL(&bytes_zval, (char*)ulid_bytes, ULID_TOTAL_BYTES);
    zend_call_method(Z_OBJ_P(return_value), php_identifier_bit128_ce, NULL, "__construct", 11, NULL, 1, &bytes_zval, NULL);
    zval_dtor(&bytes_zval);
}

/* Manual Base32 Crockford encoding for ULID (exactly 26 chars) */
static void ulid_encode_base32(const unsigned char *bytes, char *output)
{
    const char *alphabet = "0123456789ABCDEFGHJKMNPQRSTVWXYZ";

    /* Convert 16 bytes (128 bits) to 26 base32 characters (130 bits, 2 padding bits) */
    uint64_t high = 0, low = 0;

    /* Load bytes into two 64-bit integers */
    for (int i = 0; i < 8; i++) {
        high = (high << 8) | bytes[i];
        low = (low << 8) | bytes[i + 8];
    }

    /* Extract 5-bit chunks and encode */
    for (int i = 25; i >= 0; i--) {
        int bit_pos = i * 5;
        uint32_t value;

        if (bit_pos >= 64) {
            /* High part */
            value = (high >> (bit_pos - 64)) & 0x1F;
        } else if (bit_pos >= 59) {
            /* Spans both parts */
            int high_bits = bit_pos - 59;
            value = ((high & ((1ULL << (high_bits + 5)) - 1)) << (5 - high_bits)) |
                    (low >> (64 - (5 - high_bits)));
            value &= 0x1F;
        } else {
            /* Low part */
            value = (low >> bit_pos) & 0x1F;
        }

        output[25 - i] = alphabet[value];
    }

    output[26] = '\0';
}

/**
 * Convert ULID to string representation
 *
 * Returns the ULID in its canonical 26-character Crockford Base32 encoding.
 * This encoding is case-insensitive and excludes ambiguous characters.
 *
 * @return string 26-character ULID string
 *
 * @example
 * $ulid = Ulid::generate();
 * echo $ulid->toString(); // "01ARZ3NDEKTSV4RRFFQ69G5FAV"
 *
 * // Can also use string casting
 * echo (string) $ulid; // Same result
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Ulid, toString)
{
    /* Get the bytes from parent Bit128 class */
    zval bytes_result;
    zval *this_ptr = getThis();
    zend_call_method(Z_OBJ_P(this_ptr), php_identifier_bit128_ce, NULL, "getbytes", 8, &bytes_result, 0, NULL, NULL);

    if (Z_TYPE(bytes_result) != IS_STRING || Z_STRLEN(bytes_result) != ULID_TOTAL_BYTES) {
        zval_dtor(&bytes_result);
        RETURN_EMPTY_STRING();
    }

    /* Encode using manual ULID Base32 encoding */
    char ulid_str[ULID_STRING_LENGTH + 1];
    ulid_encode_base32((unsigned char*)Z_STRVAL(bytes_result), ulid_str);

    zval_dtor(&bytes_result);

    RETURN_STRINGL(ulid_str, ULID_STRING_LENGTH);
}

static PHP_METHOD(Php_Identifier_Ulid, __toString)
{
    /* Delegate to toString */
    PHP_MN(Php_Identifier_Ulid_toString)(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

/* Manual Base32 Crockford decoding for ULID */
static int ulid_decode_base32(const char *input, unsigned char *bytes)
{
    /* Create lookup table */
    int lookup[256];
    const char *alphabet = "0123456789ABCDEFGHJKMNPQRSTVWXYZ";

    for (int i = 0; i < 256; i++) {
        lookup[i] = -1;
    }
    for (int i = 0; i < 32; i++) {
        lookup[(unsigned char)alphabet[i]] = i;
    }

    /* Validate and decode */
    uint64_t high = 0, low = 0;

    for (int i = 0; i < 26; i++) {
        int value = lookup[(unsigned char)input[i]];
        if (value == -1) {
            return 0; /* Invalid character */
        }

        int bit_pos = (25 - i) * 5;

        if (bit_pos >= 64) {
            /* High part */
            high |= ((uint64_t)value) << (bit_pos - 64);
        } else if (bit_pos >= 59) {
            /* Spans both parts */
            int high_bits = bit_pos - 59;
            high |= ((uint64_t)value) >> (5 - high_bits);
            low |= ((uint64_t)(value & ((1 << (5 - high_bits)) - 1))) << (64 - (5 - high_bits));
        } else {
            /* Low part */
            low |= ((uint64_t)value) << bit_pos;
        }
    }

    /* Convert back to bytes */
    for (int i = 0; i < 8; i++) {
        bytes[i] = (high >> (56 - i * 8)) & 0xFF;
        bytes[i + 8] = (low >> (56 - i * 8)) & 0xFF;
    }

    return 1; /* Success */
}

static PHP_METHOD(Php_Identifier_Ulid, fromString)
{
    zend_string *ulid_str;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(ulid_str)
    ZEND_PARSE_PARAMETERS_END();

    /* Validate ULID string length */
    if (ZSTR_LEN(ulid_str) != ULID_STRING_LENGTH) {
        zend_throw_exception(zend_ce_exception, "Invalid ULID string length", 0);
        RETURN_THROWS();
    }

    /* Decode using manual ULID Base32 decoding */
    unsigned char bytes[ULID_TOTAL_BYTES];
    if (!ulid_decode_base32(ZSTR_VAL(ulid_str), bytes)) {
        zend_throw_exception(zend_ce_exception, "Invalid character in ULID string", 0);
        RETURN_THROWS();
    }

    /* Create ULID object */
    object_init_ex(return_value, php_identifier_ulid_ce);

    /* Set the bytes in the Bit128 parent */
    zval bytes_zval;
    ZVAL_STRINGL(&bytes_zval, (char*)bytes, ULID_TOTAL_BYTES);
    zend_call_method(Z_OBJ_P(return_value), php_identifier_bit128_ce, NULL, "__construct", 11, NULL, 1, &bytes_zval, NULL);
    zval_dtor(&bytes_zval);
}

static PHP_METHOD(Php_Identifier_Ulid, fromHex)
{
    zend_string *hex;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(hex)
    ZEND_PARSE_PARAMETERS_END();

    /* Convert hex to bytes first */
    unsigned char bytes[16];
    const char *hex_str = ZSTR_VAL(hex);
    size_t hex_len = ZSTR_LEN(hex);

    /* Remove dashes if present and validate length */
    char clean_hex[33];
    size_t clean_len = 0;

    for (size_t i = 0; i < hex_len && clean_len < 32; i++) {
        char c = hex_str[i];
        if (c != '-') {
            if (!isxdigit(c)) {
                zend_throw_exception(zend_ce_exception, "Invalid hexadecimal character in ULID", 0);
                RETURN_THROWS();
            }
            clean_hex[clean_len++] = tolower(c);
        }
    }

    if (clean_len != 32) {
        zend_throw_exception(zend_ce_exception, "ULID hex string must be exactly 32 characters (excluding dashes)", 0);
        RETURN_THROWS();
    }

    clean_hex[32] = '\0';

    /* Convert hex string to bytes */
    for (int i = 0; i < 16; i++) {
        char hex_byte[3] = {clean_hex[i*2], clean_hex[i*2+1], '\0'};
        bytes[i] = (unsigned char)strtol(hex_byte, NULL, 16);
    }

    /* Create ULID object */
    object_init_ex(return_value, php_identifier_ulid_ce);

    /* Set the bytes in the Bit128 parent */
    zval bytes_zval;
    ZVAL_STRINGL(&bytes_zval, (char*)bytes, 16);
    zend_call_method(Z_OBJ_P(return_value), php_identifier_bit128_ce, NULL, "__construct", 11, NULL, 1, &bytes_zval, NULL);
    zval_dtor(&bytes_zval);
}

static PHP_METHOD(Php_Identifier_Ulid, fromBytes)
{
    zend_string *bytes;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(bytes)
    ZEND_PARSE_PARAMETERS_END();

    /* Validate byte length */
    if (ZSTR_LEN(bytes) != 16) {
        zend_throw_exception(zend_ce_exception, "ULID bytes must be exactly 16 bytes", 0);
        RETURN_THROWS();
    }

    /* Create ULID object */
    object_init_ex(return_value, php_identifier_ulid_ce);

    /* Set the bytes in the Bit128 parent */
    zval bytes_zval;
    ZVAL_STRINGL(&bytes_zval, ZSTR_VAL(bytes), ZSTR_LEN(bytes));
    zend_call_method(Z_OBJ_P(return_value), php_identifier_bit128_ce, NULL, "__construct", 11, NULL, 1, &bytes_zval, NULL);
    zval_dtor(&bytes_zval);
}

/**
 * Get the timestamp component of the ULID
 *
 * Extracts and returns the 48-bit timestamp from the first 6 bytes of the ULID.
 * This represents milliseconds since Unix epoch (January 1, 1970).
 *
 * @return int Timestamp in milliseconds since Unix epoch
 *
 * @example
 * $ulid = Ulid::generate();
 * $timestamp = $ulid->getTimestamp();
 * echo date('Y-m-d H:i:s', $timestamp / 1000); // Convert to readable date
 *
 * // ULIDs are sortable by timestamp
 * $ulid1 = Ulid::generate();
 * usleep(1000); // Wait 1ms
 * $ulid2 = Ulid::generate();
 * var_dump($ulid1->getTimestamp() < $ulid2->getTimestamp()); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Ulid, getTimestamp)
{
    /* Get the bytes from parent Bit128 class */
    zval bytes_result;
    zval *this_ptr = getThis();
    zend_call_method(Z_OBJ_P(this_ptr), php_identifier_bit128_ce, NULL, "getbytes", 8, &bytes_result, 0, NULL, NULL);

    if (Z_TYPE(bytes_result) != IS_STRING || Z_STRLEN(bytes_result) != ULID_TOTAL_BYTES) {
        zval_dtor(&bytes_result);
        RETURN_LONG(0);
    }

    /* Extract timestamp from first 6 bytes (big-endian) */
    const unsigned char *bytes = (unsigned char*)Z_STRVAL(bytes_result);
    uint64_t timestamp = 0;

    for (int i = 0; i < ULID_TIMESTAMP_BYTES; i++) {
        timestamp = (timestamp << 8) | bytes[i];
    }

    zval_dtor(&bytes_result);
    RETURN_LONG(timestamp);
}

/**
 * Get the randomness component of the ULID
 *
 * Extracts and returns the 80-bit randomness from the last 10 bytes of the ULID.
 * This provides uniqueness when multiple ULIDs are generated in the same millisecond.
 *
 * @return string 10-byte binary randomness data
 *
 * @example
 * $ulid = Ulid::generate();
 * $randomness = $ulid->getRandomness();
 * echo strlen($randomness); // 10
 * echo bin2hex($randomness); // 20-character hex string
 *
 * // Different ULIDs have different randomness
 * $ulid1 = Ulid::generate();
 * $ulid2 = Ulid::generate();
 * var_dump($ulid1->getRandomness() !== $ulid2->getRandomness()); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Ulid, getRandomness)
{
    /* Get the bytes from parent Bit128 class */
    zval bytes_result;
    zval *this_ptr = getThis();
    zend_call_method(Z_OBJ_P(this_ptr), php_identifier_bit128_ce, NULL, "getbytes", 8, &bytes_result, 0, NULL, NULL);

    if (Z_TYPE(bytes_result) != IS_STRING || Z_STRLEN(bytes_result) != ULID_TOTAL_BYTES) {
        zval_dtor(&bytes_result);
        RETURN_EMPTY_STRING();
    }

    /* Extract randomness from last 10 bytes */
    const char *bytes = Z_STRVAL(bytes_result);
    zend_string *randomness = zend_string_init(bytes + ULID_TIMESTAMP_BYTES, ULID_RANDOMNESS_BYTES, 0);

    zval_dtor(&bytes_result);
    RETURN_STR(randomness);
}

/* ULID method entries */
static const zend_function_entry php_identifier_ulid_methods[] = {
    PHP_ME(Php_Identifier_Ulid, generate, arginfo_ulid_generate, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Ulid, toString, arginfo_ulid_toString, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Ulid, __toString, arginfo_ulid_toString, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Ulid, fromString, arginfo_ulid_fromString, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Ulid, fromHex, arginfo_ulid_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Ulid, fromBytes, arginfo_ulid_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Ulid, getTimestamp, arginfo_ulid_getTimestamp, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Ulid, getRandomness, arginfo_ulid_getRandomness, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* Register ULID class */
void php_identifier_ulid_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier", "Ulid", php_identifier_ulid_methods);
    php_identifier_ulid_ce = zend_register_internal_class_ex(&ce, php_identifier_bit128_ce);
    php_identifier_ulid_ce->ce_flags |= ZEND_ACC_FINAL;
}
