#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <string.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version4_generate, 0, 0, Php\\Identifier\\Uuid\\Version4, 0)
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, context, Php\\Identifier\\Context, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version4_fromString, 0, 1, Php\\Identifier\\Uuid\\Version4, 0)
    ZEND_ARG_TYPE_INFO(0, uuid, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version4_fromBytes, 0, 1, Php\\Identifier\\Uuid\\Version4, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version4_fromHex, 0, 1, Php\\Identifier\\Uuid\\Version4, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version4_getRandomBytes, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_version4_getPureRandomBytes, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

/* UUID Version 4 methods */
static PHP_METHOD(Php_Identifier_Uuid_Version4, generate)
{
    zval *context = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_OBJECT_OF_CLASS_OR_NULL(context, php_identifier_context_ce)
    ZEND_PARSE_PARAMETERS_END();

    /* Generate 16 random bytes */
    unsigned char uuid_bytes[16];

    if (context != NULL) {
        /* Use provided context for random bytes */
        zval function_name;
        zval params[1];
        zval random_result;

        ZVAL_STRING(&function_name, "getRandomBytes");
        ZVAL_LONG(&params[0], 16);

        if (call_user_function(NULL, context, &function_name, &random_result, 1, params) == SUCCESS) {
            if (Z_TYPE(random_result) == IS_STRING && Z_STRLEN(random_result) == 16) {
                memcpy(uuid_bytes, Z_STRVAL(random_result), 16);
            } else {
                zval_ptr_dtor(&function_name);
                zval_ptr_dtor(&random_result);
                zend_throw_exception(zend_ce_exception, "Context getRandomBytes did not return 16 bytes", 0);
                RETURN_THROWS();
            }
            zval_ptr_dtor(&random_result);
        } else {
            zval_ptr_dtor(&function_name);
            zend_throw_exception(zend_ce_exception, "Failed to call getRandomBytes on context", 0);
            RETURN_THROWS();
        }
        zval_ptr_dtor(&function_name);
    } else {
        /* Use system random bytes */
        php_identifier_generate_random_bytes(uuid_bytes, 16);
    }

    /* Set version bits: version 4 (0100) in the most significant 4 bits of byte 6 */
    uuid_bytes[6] = (uuid_bytes[6] & 0x0F) | 0x40;

    /* Set variant bits: variant 10 in the most significant 2 bits of byte 8 */
    uuid_bytes[8] = (uuid_bytes[8] & 0x3F) | 0x80;

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version4_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid_Version4, fromString)
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

    /* Check if it's version 4 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 4) {
        zend_throw_exception(zend_ce_exception, "UUID string is not version 4", 0);
        RETURN_THROWS();
    }

    /* Create Version4 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version4_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid_Version4, fromBytes)
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

    /* Check if it's version 4 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 4) {
        zend_throw_exception(zend_ce_exception, "Bytes do not represent a version 4 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version4 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version4_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid_Version4, fromHex)
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

    /* Check if it's version 4 */
    int version = (bytes[6] >> 4) & 0x0F;
    if (version != 4) {
        zend_throw_exception(zend_ce_exception, "Hex does not represent a version 4 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version4 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version4_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid_Version4, getRandomBytes)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Return all 16 bytes (including version and variant bits) */
    zend_string *random_bytes = zend_string_alloc(16, 0);
    memcpy(ZSTR_VAL(random_bytes), intern->data, 16);
    ZSTR_VAL(random_bytes)[16] = '\0';

    RETURN_STR(random_bytes);
}

static PHP_METHOD(Php_Identifier_Uuid_Version4, getPureRandomBytes)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());

    /* Return the random bytes with version and variant bits cleared */
    zend_string *pure_bytes = zend_string_alloc(16, 0);
    memcpy(ZSTR_VAL(pure_bytes), intern->data, 16);

    /* Clear version bits (byte 6, upper nibble) */
    ZSTR_VAL(pure_bytes)[6] &= 0x0F;

    /* Clear variant bits (byte 8, upper 2 bits) */
    ZSTR_VAL(pure_bytes)[8] &= 0x3F;

    ZSTR_VAL(pure_bytes)[16] = '\0';

    RETURN_STR(pure_bytes);
}

/* UUID Version 4 method entries */
static const zend_function_entry php_identifier_uuid_version4_methods[] = {
    PHP_ME(Php_Identifier_Uuid_Version4, generate, arginfo_uuid_version4_generate, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version4, fromString, arginfo_uuid_version4_fromString, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version4, fromBytes, arginfo_uuid_version4_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version4, fromHex, arginfo_uuid_version4_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid_Version4, getRandomBytes, arginfo_uuid_version4_getRandomBytes, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid_Version4, getPureRandomBytes, arginfo_uuid_version4_getPureRandomBytes, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* Register UUID Version 4 class */
void php_identifier_uuid_version4_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier\\Uuid", "Version4", php_identifier_uuid_version4_methods);
    php_identifier_uuid_version4_ce = zend_register_internal_class_ex(&ce, php_identifier_uuid_ce);
    php_identifier_uuid_version4_ce->ce_flags |= ZEND_ACC_FINAL;
}
