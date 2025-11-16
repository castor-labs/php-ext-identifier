#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <ctype.h>
#include <string.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version3_generate, 0, 2, Identifier\\Uuid\\Version3, 0)
    ZEND_ARG_TYPE_INFO(0, namespace, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, name, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version3_fromString, 0, 1, Identifier\\Uuid\\Version3, 0)
    ZEND_ARG_TYPE_INFO(0, uuid, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version3_fromBytes, 0, 1, Identifier\\Uuid\\Version3, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version3_fromHex, 0, 1, Identifier\\Uuid\\Version3, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()



/* Simple MD5 implementation for UUID v3 */
static void simple_md5(const unsigned char *input, size_t len, unsigned char output[16])
{
    /* Use PHP's hash function */
    zval function_name;
    zval params[1];
    zval result;

    ZVAL_STRING(&function_name, "md5");
    ZVAL_STRINGL(&params[0], (char*)input, len);

    if (call_user_function(NULL, NULL, &function_name, &result, 1, params) == SUCCESS) {
        if (Z_TYPE(result) == IS_STRING && Z_STRLEN(result) == 32) {
            /* Convert hex string to bytes */
            for (int i = 0; i < 16; i++) {
                char hex_byte[3] = {Z_STRVAL(result)[i*2], Z_STRVAL(result)[i*2+1], '\0'};
                output[i] = (unsigned char)strtol(hex_byte, NULL, 16);
            }
        }
        zval_ptr_dtor(&result);
    }

    zval_ptr_dtor(&function_name);
    zval_ptr_dtor(&params[0]);
}

/* UUID Version 3 methods */

/**
 * Generate a new UUID version 3 (name-based with MD5)
 *
 * Creates a UUID version 3 by hashing a namespace UUID and name using MD5.
 * The same namespace and name will always produce the same UUID, making
 * this suitable for deterministic identifier generation.
 *
 * @param string $namespace UUID namespace as string
 * @param string $name Name to hash within the namespace
 * @return Version3 A new UUID version 3 instance
 * @throws Exception If namespace is invalid or hashing fails
 *
 * @example
 * // Generate deterministic UUID from namespace and name
 * $namespace = "6ba7b810-9dad-11d1-80b4-00c04fd430c8";
 * $uuid = Version3::generate($namespace, "example.com");
 * echo $uuid->toString(); // Always the same for these inputs
 *
 * // Same inputs always produce same UUID
 * $uuid2 = Version3::generate($namespace, "example.com");
 * var_dump($uuid->equals($uuid2)); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_Uuid_Version3, generate)
{
    zval *namespace_uuid;
    zend_string *name;

    ZEND_PARSE_PARAMETERS_START(2, 2)
        Z_PARAM_OBJECT_OF_CLASS(namespace_uuid, php_identifier_uuid_ce)
        Z_PARAM_STR(name)
    ZEND_PARSE_PARAMETERS_END();

    /* Get namespace UUID bytes */
    php_identifier_bit128_obj *ns_intern = PHP_IDENTIFIER_BIT128_OBJ_P(namespace_uuid);

    /* Create input for MD5: namespace bytes + name bytes */
    size_t input_len = 16 + ZSTR_LEN(name);
    unsigned char *input = emalloc(input_len);

    /* Copy namespace UUID bytes */
    memcpy(input, ns_intern->data, 16);

    /* Copy name bytes */
    memcpy(input + 16, ZSTR_VAL(name), ZSTR_LEN(name));

    /* Calculate MD5 hash */
    unsigned char hash[16]; /* MD5 produces 16 bytes */
    simple_md5(input, input_len, hash);

    efree(input);

    /* Use the hash directly as UUID bytes (MD5 is exactly 16 bytes) */
    unsigned char uuid_bytes[16];
    memcpy(uuid_bytes, hash, 16);

    /* Set version bits: version 3 (0011) in the most significant 4 bits of byte 6 */
    uuid_bytes[6] = (uuid_bytes[6] & 0x0F) | 0x30;

    /* Set variant bits: variant 10 in the most significant 2 bits of byte 8 */
    uuid_bytes[8] = (uuid_bytes[8] & 0x3F) | 0x80;

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version3_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Identifier_Uuid_Version3, fromString)
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

    /* Check if it's version 3 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 3) {
        zend_throw_exception(zend_ce_exception, "UUID string is not version 3", 0);
        RETURN_THROWS();
    }

    /* Create Version3 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version3_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Identifier_Uuid_Version3, fromBytes)
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

    /* Check if it's version 3 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 3) {
        zend_throw_exception(zend_ce_exception, "Bytes do not represent a version 3 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version3 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version3_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Identifier_Uuid_Version3, fromHex)
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

    /* Check if it's version 3 */
    int version = (bytes[6] >> 4) & 0x0F;
    if (version != 3) {
        zend_throw_exception(zend_ce_exception, "Hex does not represent a version 3 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version3 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version3_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/* UUID Version 3 method entries */
static const zend_function_entry php_identifier_uuid_version3_methods[] = {
    PHP_ME(Identifier_Uuid_Version3, generate, arginfo_uuid_version3_generate, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_Uuid_Version3, fromString, arginfo_uuid_version3_fromString, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_Uuid_Version3, fromBytes, arginfo_uuid_version3_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_Uuid_Version3, fromHex, arginfo_uuid_version3_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_FE_END
};

/* Register UUID Version 3 class */
void php_identifier_uuid_version3_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Identifier\\Uuid", "Version3", php_identifier_uuid_version3_methods);
    php_identifier_uuid_version3_ce = zend_register_internal_class_ex(&ce, php_identifier_uuid_ce);
    php_identifier_uuid_version3_ce->ce_flags |= ZEND_ACC_FINAL;
}
