#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <ctype.h>
#include <string.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version7_generate, 0, 0, Php\\Identifier\\Uuid\\Version7, 0)
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, context, Php\\Identifier\\Context, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version7_fromString, 0, 1, Php\\Identifier\\Uuid\\Version7, 0)
    ZEND_ARG_TYPE_INFO(0, uuid, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version7_fromBytes, 0, 1, Php\\Identifier\\Uuid\\Version7, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version7_fromHex, 0, 1, Php\\Identifier\\Uuid\\Version7, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version7_getTimestamp, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version7_getRandomBytes, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version7_getRandomA, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version7_getRandomB, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

/* UUID Version 7 methods */

/**
 * Generate a new UUID version 7 (Unix timestamp-based)
 *
 * Creates a UUID version 7 with a Unix timestamp in milliseconds followed by
 * random data. This provides natural sorting by creation time and is the
 * recommended UUID version for new applications.
 *
 * @param Context|null $context Optional context for controlling time and randomness
 * @return Version7 A new UUID version 7 instance
 * @throws Exception If timestamp or random generation fails
 *
 * @example
 * // Generate with current timestamp
 * $uuid = Version7::generate();
 * echo $uuid->toString(); // "018c2e65-4b0a-7c3d-8f2e-1a4b5c6d7e8f"
 *
 * // UUIDs are naturally sortable by timestamp
 * $uuid1 = Version7::generate();
 * usleep(1000);
 * $uuid2 = Version7::generate();
 * var_dump($uuid1->toString() < $uuid2->toString()); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, generate)
{
    zval *context = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_OBJECT_OF_CLASS_OR_NULL(context, php_identifier_context_ce)
    ZEND_PARSE_PARAMETERS_END();

    unsigned char uuid_bytes[16];
    uint64_t timestamp_ms;

    if (context != NULL) {
        /* Get timestamp from context */
        zval function_name;
        zval timestamp_result;

        ZVAL_STRING(&function_name, "getTimestampMs");

        if (call_user_function(NULL, context, &function_name, &timestamp_result, 0, NULL) == SUCCESS) {
            if (Z_TYPE(timestamp_result) == IS_LONG) {
                timestamp_ms = (uint64_t)Z_LVAL(timestamp_result);
            } else {
                zval_ptr_dtor(&function_name);
                zval_ptr_dtor(&timestamp_result);
                zend_throw_exception(zend_ce_exception, "Context getTimestampMs did not return a number", 0);
                RETURN_THROWS();
            }
            zval_ptr_dtor(&timestamp_result);
        } else {
            zval_ptr_dtor(&function_name);
            zend_throw_exception(zend_ce_exception, "Failed to call getTimestampMs on context", 0);
            RETURN_THROWS();
        }
        zval_ptr_dtor(&function_name);

        /* Get random bytes from context */
        zval random_function;
        zval random_params[1];
        zval random_result;

        ZVAL_STRING(&random_function, "getRandomBytes");
        ZVAL_LONG(&random_params[0], 10); /* Need 10 random bytes for v7 */

        if (call_user_function(NULL, context, &random_function, &random_result, 1, random_params) == SUCCESS) {
            if (Z_TYPE(random_result) == IS_STRING && Z_STRLEN(random_result) == 10) {
                /* Copy random bytes to appropriate positions */
                memcpy(&uuid_bytes[6], Z_STRVAL(random_result), 2);  /* 12 bits after timestamp */
                memcpy(&uuid_bytes[8], Z_STRVAL(random_result) + 2, 8); /* 62 bits of random data */
            } else {
                zval_ptr_dtor(&random_function);
                zval_ptr_dtor(&random_result);
                zend_throw_exception(zend_ce_exception, "Context getRandomBytes did not return 10 bytes", 0);
                RETURN_THROWS();
            }
            zval_ptr_dtor(&random_result);
        } else {
            zval_ptr_dtor(&random_function);
            zend_throw_exception(zend_ce_exception, "Failed to call getRandomBytes on context", 0);
            RETURN_THROWS();
        }
        zval_ptr_dtor(&random_function);
    } else {
        /* Use system time and random */
        timestamp_ms = php_identifier_get_timestamp_ms();
        php_identifier_generate_random_bytes(&uuid_bytes[6], 10);
    }

    /* Set the 48-bit timestamp in big-endian format (bytes 0-5) */
    uuid_bytes[0] = (timestamp_ms >> 40) & 0xFF;
    uuid_bytes[1] = (timestamp_ms >> 32) & 0xFF;
    uuid_bytes[2] = (timestamp_ms >> 24) & 0xFF;
    uuid_bytes[3] = (timestamp_ms >> 16) & 0xFF;
    uuid_bytes[4] = (timestamp_ms >> 8) & 0xFF;
    uuid_bytes[5] = timestamp_ms & 0xFF;

    /* Set version bits: version 7 (0111) in the most significant 4 bits of byte 6 */
    uuid_bytes[6] = (uuid_bytes[6] & 0x0F) | 0x70;

    /* Set variant bits: variant 10 in the most significant 2 bits of byte 8 */
    uuid_bytes[8] = (uuid_bytes[8] & 0x3F) | 0x80;

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version7_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/**
 * Get the timestamp from UUID version 7
 *
 * Extracts the 48-bit Unix timestamp in milliseconds from the UUID version 7.
 * This represents the time when the UUID was generated.
 *
 * @return int Unix timestamp in milliseconds
 *
 * @example
 * $uuid = Version7::generate();
 * $timestamp = $uuid->getTimestamp();
 * echo date('Y-m-d H:i:s', $timestamp / 1000); // Convert to readable date
 *
 * // Compare timestamps
 * $uuid1 = Version7::generate();
 * usleep(1000);
 * $uuid2 = Version7::generate();
 * var_dump($uuid1->getTimestamp() < $uuid2->getTimestamp()); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, getTimestamp)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Extract 48-bit timestamp from bytes 0-5 (big-endian) */
    uint64_t timestamp_ms = 0;
    timestamp_ms |= ((uint64_t)intern->data[0]) << 40;
    timestamp_ms |= ((uint64_t)intern->data[1]) << 32;
    timestamp_ms |= ((uint64_t)intern->data[2]) << 24;
    timestamp_ms |= ((uint64_t)intern->data[3]) << 16;
    timestamp_ms |= ((uint64_t)intern->data[4]) << 8;
    timestamp_ms |= ((uint64_t)intern->data[5]);

    RETURN_LONG(timestamp_ms);
}

/**
 * Create UUID version 7 from string representation
 *
 * Parses a UUID version 7 from its standard string representation.
 * The string must be in the format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
 * and must be a valid version 7 UUID.
 *
 * @param string $uuid UUID string in standard format
 * @return Version7 A new UUID version 7 instance
 * @throws Exception If string is invalid or not version 7
 *
 * @example
 * $uuid = Version7::fromString("018c2e65-4b0a-7c3d-8f2e-1a4b5c6d7e8f");
 * echo $uuid->getVersion(); // 7
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, fromString)
{
    zend_string *uuid_str;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(uuid_str)
    ZEND_PARSE_PARAMETERS_END();

    /* Validate UUID string format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx */
    if (ZSTR_LEN(uuid_str) != 36) {
        zend_throw_exception(zend_ce_exception, "Invalid UUID string length", 0);
        RETURN_THROWS();
    }

    const char *str = ZSTR_VAL(uuid_str);

    /* Check hyphens are in correct positions */
    if (str[8] != '-' || str[13] != '-' || str[18] != '-' || str[23] != '-') {
        zend_throw_exception(zend_ce_exception, "Invalid UUID string format", 0);
        RETURN_THROWS();
    }

    /* Parse hex digits and convert to bytes */
    unsigned char uuid_bytes[16];
    int byte_index = 0;

    for (int i = 0; i < 36; i++) {
        if (i == 8 || i == 13 || i == 18 || i == 23) {
            continue; /* Skip hyphens */
        }

        char hex_char1 = str[i];
        char hex_char2 = str[i + 1];

        /* Validate hex characters */
        if (!isxdigit(hex_char1) || !isxdigit(hex_char2)) {
            zend_throw_exception(zend_ce_exception, "Invalid hex characters in UUID string", 0);
            RETURN_THROWS();
        }

        /* Convert hex pair to byte */
        char hex_pair[3] = {hex_char1, hex_char2, '\0'};
        uuid_bytes[byte_index] = (unsigned char)strtol(hex_pair, NULL, 16);
        byte_index++;
        i++; /* Skip the second hex character */
    }

    /* Check if it's version 7 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 7) {
        zend_throw_exception(zend_ce_exception, "UUID string is not version 7", 0);
        RETURN_THROWS();
    }

    /* Create Version7 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version7_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/**
 * Create UUID version 7 from binary data
 *
 * Creates a UUID version 7 instance from exactly 16 bytes of binary data.
 * The binary data must represent a valid version 7 UUID.
 *
 * @param string $bytes Exactly 16 bytes of binary data
 * @return Version7 A new UUID version 7 instance
 * @throws Exception If bytes is not exactly 16 bytes or not version 7
 *
 * @example
 * $bytes = hex2bin("018c2e654b0a7c3d8f2e1a4b5c6d7e8f");
 * $uuid = Version7::fromBytes($bytes);
 * echo $uuid->getVersion(); // 7
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, fromBytes)
{
    zend_string *bytes;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(bytes)
    ZEND_PARSE_PARAMETERS_END();

    /* Validate byte length */
    if (ZSTR_LEN(bytes) != 16) {
        zend_throw_exception(zend_ce_exception, "UUID bytes must be exactly 16 bytes", 0);
        RETURN_THROWS();
    }

    const unsigned char *uuid_bytes = (const unsigned char *)ZSTR_VAL(bytes);

    /* Check if it's version 7 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 7) {
        zend_throw_exception(zend_ce_exception, "Bytes do not represent a version 7 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version7 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version7_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/**
 * Create UUID version 7 from hexadecimal string
 *
 * Creates a UUID version 7 instance from a 32-character hexadecimal string.
 * The hex string can be with or without hyphens and is case-insensitive.
 *
 * @param string $hex 32-character hexadecimal string (with or without hyphens)
 * @return Version7 A new UUID version 7 instance
 * @throws Exception If hex string is invalid or not version 7
 *
 * @example
 * $uuid = Version7::fromHex("018c2e654b0a7c3d8f2e1a4b5c6d7e8f");
 * echo $uuid->toString(); // "018c2e65-4b0a-7c3d-8f2e-1a4b5c6d7e8f"
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, fromHex)
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
                zend_throw_exception(zend_ce_exception, "Invalid hexadecimal character in UUID", 0);
                RETURN_THROWS();
            }
            clean_hex[clean_len++] = tolower(c);
        }
    }

    if (clean_len != 32) {
        zend_throw_exception(zend_ce_exception, "UUID hex string must be exactly 32 characters (excluding dashes)", 0);
        RETURN_THROWS();
    }

    clean_hex[32] = '\0';

    /* Convert hex string to bytes */
    for (int i = 0; i < 16; i++) {
        char hex_byte[3] = {clean_hex[i*2], clean_hex[i*2+1], '\0'};
        bytes[i] = (unsigned char)strtol(hex_byte, NULL, 16);
    }

    /* Check if it's version 7 */
    int version = (bytes[6] >> 4) & 0x0F;
    if (version != 7) {
        zend_throw_exception(zend_ce_exception, "Hex does not represent a version 7 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version7 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version7_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/**
 * Get all random bytes from UUID version 7
 *
 * Extracts the 74 bits of random data from the UUID version 7 as 10 bytes.
 * This includes both the rand_a and rand_b fields.
 *
 * @return string 10 bytes of random data
 *
 * @example
 * $uuid = Version7::generate();
 * $randomBytes = $uuid->getRandomBytes();
 * echo strlen($randomBytes); // 10
 * echo bin2hex($randomBytes); // e.g., "3d8f2e1a4b5c6d7e8f90"
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, getRandomBytes)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Extract the random portion (bytes 6-15, excluding version and variant bits) */
    zend_string *random_bytes = zend_string_alloc(10, 0);

    /* Copy bytes 6-15 */
    memcpy(ZSTR_VAL(random_bytes), &intern->data[6], 10);

    /* Clear version bits from byte 6 (first byte of random portion) */
    ZSTR_VAL(random_bytes)[0] &= 0x0F;

    /* Clear variant bits from byte 8 (third byte of random portion) */
    ZSTR_VAL(random_bytes)[2] &= 0x3F;

    ZSTR_VAL(random_bytes)[10] = '\0';

    RETURN_STR(random_bytes);
}

/**
 * Get the rand_a field from UUID version 7
 *
 * Extracts the 12-bit rand_a field from the UUID version 7. This is the
 * first random component after the timestamp.
 *
 * @return int 12-bit random value (0-4095)
 *
 * @example
 * $uuid = Version7::generate();
 * $randA = $uuid->getRandomA();
 * echo $randA; // e.g., 3245 (0-4095)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, getRandomA)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Extract rand_a (12 bits from byte 6, after version bits) */
    uint16_t rand_a = ((uint16_t)(intern->data[6] & 0x0F) << 8) |
                      ((uint16_t)intern->data[7]);

    RETURN_LONG(rand_a);
}

/**
 * Get the rand_b field from UUID version 7
 *
 * Extracts the 62-bit rand_b field from the UUID version 7 as 8 bytes.
 * This is the main random component providing uniqueness.
 *
 * @return string 8 bytes of random data (rand_b field)
 *
 * @example
 * $uuid = Version7::generate();
 * $randB = $uuid->getRandomB();
 * echo strlen($randB); // 8
 * echo bin2hex($randB); // e.g., "8f2e1a4b5c6d7e8f"
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid_Version7, getRandomB)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Extract rand_b (62 bits from bytes 8-15, after variant bits) */
    uint64_t rand_b = 0;

    /* Start with byte 8, clear variant bits */
    rand_b |= ((uint64_t)(intern->data[8] & 0x3F)) << 56;

    /* Add bytes 9-15 */
    for (int i = 9; i < 16; i++) {
        rand_b |= ((uint64_t)intern->data[i]) << (8 * (15 - i));
    }

    RETURN_LONG(rand_b);
}

/* UUID Version 7 method entries */
static const zend_function_entry php_identifier_uuid_version7_methods[] = {
    PHP_ME(Php_Identifier_Uuid_Version7, generate, arginfo_uuid_version7_generate, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version7, fromString, arginfo_uuid_version7_fromString, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version7, fromBytes, arginfo_uuid_version7_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version7, fromHex, arginfo_uuid_version7_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version7, getTimestamp, arginfo_uuid_version7_getTimestamp, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid_Version7, getRandomBytes, arginfo_uuid_version7_getRandomBytes, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid_Version7, getRandomA, arginfo_uuid_version7_getRandomA, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid_Version7, getRandomB, arginfo_uuid_version7_getRandomB, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* Register UUID Version 7 class */
void php_identifier_uuid_version7_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier\\Uuid", "Version7", php_identifier_uuid_version7_methods);
    php_identifier_uuid_version7_ce = zend_register_internal_class_ex(&ce, php_identifier_uuid_ce);
    php_identifier_uuid_version7_ce->ce_flags |= ZEND_ACC_FINAL;
}
