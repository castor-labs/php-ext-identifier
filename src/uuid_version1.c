#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <string.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version1_generate, 0, 0, Php\\Identifier\\Uuid\\Version1, 0)
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, context, Php\\Identifier\\Context, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version1_fromString, 0, 1, Php\\Identifier\\Uuid\\Version1, 0)
    ZEND_ARG_TYPE_INFO(0, uuid, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version1_fromBytes, 0, 1, Php\\Identifier\\Uuid\\Version1, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version1_fromHex, 0, 1, Php\\Identifier\\Uuid\\Version1, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version1_getTimestamp, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version1_getNode, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version1_getClockSequence, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

/* UUID Version 1 methods */
static PHP_METHOD(Php_Identifier_Uuid_Version1, generate)
{
    zval *context = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_OBJECT_OF_CLASS_OR_NULL(context, php_identifier_context_ce)
    ZEND_PARSE_PARAMETERS_END();

    unsigned char uuid_bytes[16];
    uint64_t timestamp_100ns;
    uint16_t clock_seq;
    unsigned char node[6];

    if (context != NULL) {
        /* Get timestamp from context (convert ms to 100ns units) */
        zval function_name;
        zval timestamp_result;

        ZVAL_STRING(&function_name, "getTimestampMs");

        if (call_user_function(NULL, context, &function_name, &timestamp_result, 0, NULL) == SUCCESS) {
            if (Z_TYPE(timestamp_result) == IS_LONG) {
                /* Convert milliseconds to 100-nanosecond units since UUID epoch (1582-10-15) */
                uint64_t timestamp_ms = (uint64_t)Z_LVAL(timestamp_result);
                /* UUID epoch is 122192928000000000 100ns units before Unix epoch */
                timestamp_100ns = (timestamp_ms * 10000) + 122192928000000000ULL;
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

        /* Get random bytes for clock sequence and node */
        zval random_function;
        zval random_params[1];
        zval random_result;

        ZVAL_STRING(&random_function, "getRandomBytes");
        ZVAL_LONG(&random_params[0], 8); /* 2 bytes for clock_seq + 6 bytes for node */

        if (call_user_function(NULL, context, &random_function, &random_result, 1, random_params) == SUCCESS) {
            if (Z_TYPE(random_result) == IS_STRING && Z_STRLEN(random_result) == 8) {
                /* Extract clock sequence (14 bits) */
                clock_seq = ((unsigned char)Z_STRVAL(random_result)[0] << 8) |
                           ((unsigned char)Z_STRVAL(random_result)[1]);
                clock_seq &= 0x3FFF; /* Keep only 14 bits */

                /* Extract node (6 bytes) */
                memcpy(node, Z_STRVAL(random_result) + 2, 6);
            } else {
                zval_ptr_dtor(&random_function);
                zval_ptr_dtor(&random_result);
                zend_throw_exception(zend_ce_exception, "Context getRandomBytes did not return 8 bytes", 0);
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
        uint64_t timestamp_ms = php_identifier_get_timestamp_ms();
        timestamp_100ns = (timestamp_ms * 10000) + 122192928000000000ULL;

        /* Generate random clock sequence and node */
        unsigned char random_data[8];
        php_identifier_generate_random_bytes(random_data, 8);

        clock_seq = (random_data[0] << 8) | random_data[1];
        clock_seq &= 0x3FFF; /* Keep only 14 bits */

        memcpy(node, random_data + 2, 6);
    }

    /* Set multicast bit for random node (RFC 4122 requirement) */
    node[0] |= 0x01;

    /* Build UUID v1 layout */
    /* time_low (32 bits) */
    uuid_bytes[0] = (timestamp_100ns >> 24) & 0xFF;
    uuid_bytes[1] = (timestamp_100ns >> 16) & 0xFF;
    uuid_bytes[2] = (timestamp_100ns >> 8) & 0xFF;
    uuid_bytes[3] = timestamp_100ns & 0xFF;

    /* time_mid (16 bits) */
    uuid_bytes[4] = (timestamp_100ns >> 40) & 0xFF;
    uuid_bytes[5] = (timestamp_100ns >> 32) & 0xFF;

    /* time_hi_and_version (16 bits) */
    uuid_bytes[6] = ((timestamp_100ns >> 56) & 0x0F) | 0x10; /* Version 1 */
    uuid_bytes[7] = (timestamp_100ns >> 48) & 0xFF;

    /* clock_seq_hi_and_reserved and clock_seq_low */
    uuid_bytes[8] = ((clock_seq >> 8) & 0x3F) | 0x80; /* Variant bits */
    uuid_bytes[9] = clock_seq & 0xFF;

    /* node (48 bits) */
    memcpy(&uuid_bytes[10], node, 6);

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version1_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid_Version1, getTimestamp)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Extract 60-bit timestamp from UUID v1 layout */
    uint64_t time_low = ((uint64_t)intern->data[0] << 24) |
                       ((uint64_t)intern->data[1] << 16) |
                       ((uint64_t)intern->data[2] << 8) |
                       ((uint64_t)intern->data[3]);

    uint64_t time_mid = ((uint64_t)intern->data[4] << 8) |
                       ((uint64_t)intern->data[5]);

    uint64_t time_hi = ((uint64_t)intern->data[6] & 0x0F) << 8 |
                      ((uint64_t)intern->data[7]);

    /* Reconstruct 60-bit timestamp */
    uint64_t timestamp_100ns = (time_hi << 48) | (time_mid << 32) | time_low;

    /* Convert back to milliseconds since Unix epoch */
    uint64_t timestamp_ms = (timestamp_100ns - 122192928000000000ULL) / 10000;

    RETURN_LONG(timestamp_ms);
}

static PHP_METHOD(Php_Identifier_Uuid_Version1, getNode)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Extract 48-bit node from bytes 10-15 */
    zend_string *node = zend_string_alloc(6, 0);
    memcpy(ZSTR_VAL(node), &intern->data[10], 6);
    ZSTR_VAL(node)[6] = '\0';

    RETURN_STR(node);
}

static PHP_METHOD(Php_Identifier_Uuid_Version1, getClockSequence)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Extract 14-bit clock sequence from bytes 8-9 */
    uint16_t clock_seq = (((uint16_t)intern->data[8] & 0x3F) << 8) |
                        ((uint16_t)intern->data[9]);

    RETURN_LONG(clock_seq);
}

static PHP_METHOD(Php_Identifier_Uuid_Version1, fromString)
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

    /* Check if it's version 1 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 1) {
        zend_throw_exception(zend_ce_exception, "UUID string is not version 1", 0);
        RETURN_THROWS();
    }

    /* Create Version1 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version1_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid_Version1, fromBytes)
{
    zend_string *bytes;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(bytes)
    ZEND_PARSE_PARAMETERS_END();

    if (ZSTR_LEN(bytes) != 16) {
        zend_throw_exception(zend_ce_exception, "UUID bytes must be exactly 16 bytes", 0);
        RETURN_THROWS();
    }

    const unsigned char *uuid_bytes = (const unsigned char *)ZSTR_VAL(bytes);

    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 1) {
        zend_throw_exception(zend_ce_exception, "Bytes do not represent a version 1 UUID", 0);
        RETURN_THROWS();
    }

    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version1_ce);

    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid_Version1, fromHex)
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

    /* Check if it's version 1 */
    int version = (bytes[6] >> 4) & 0x0F;
    if (version != 1) {
        zend_throw_exception(zend_ce_exception, "Hex does not represent a version 1 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version1 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version1_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/* UUID Version 1 method entries */
static const zend_function_entry php_identifier_uuid_version1_methods[] = {
    PHP_ME(Php_Identifier_Uuid_Version1, generate, arginfo_uuid_version1_generate, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version1, fromString, arginfo_uuid_version1_fromString, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version1, fromBytes, arginfo_uuid_version1_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version1, fromHex, arginfo_uuid_version1_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version1, getTimestamp, arginfo_uuid_version1_getTimestamp, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid_Version1, getNode, arginfo_uuid_version1_getNode, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid_Version1, getClockSequence, arginfo_uuid_version1_getClockSequence, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* Register UUID Version 1 class */
void php_identifier_uuid_version1_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier\\Uuid", "Version1", php_identifier_uuid_version1_methods);
    php_identifier_uuid_version1_ce = zend_register_internal_class_ex(&ce, php_identifier_uuid_ce);
    php_identifier_uuid_version1_ce->ce_flags |= ZEND_ACC_FINAL;
}
