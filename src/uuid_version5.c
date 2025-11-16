#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <ctype.h>
#include <string.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version5_generate, 0, 2, Identifier\\Uuid\\Version5, 0)
    ZEND_ARG_TYPE_INFO(0, namespace, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, name, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version5_fromString, 0, 1, Identifier\\Uuid\\Version5, 0)
    ZEND_ARG_TYPE_INFO(0, uuid, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version5_fromBytes, 0, 1, Identifier\\Uuid\\Version5, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_version5_fromHex, 0, 1, Identifier\\Uuid\\Version5, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()



/* Simple SHA-1 implementation for UUID v5 */
static void simple_sha1(const unsigned char *input, size_t len, unsigned char output[20])
{
    /* Use PHP's hash function */
    zval function_name;
    zval params[1];
    zval result;

    ZVAL_STRING(&function_name, "sha1");
    ZVAL_STRINGL(&params[0], (char*)input, len);

    if (call_user_function(NULL, NULL, &function_name, &result, 1, params) == SUCCESS) {
        if (Z_TYPE(result) == IS_STRING && Z_STRLEN(result) == 40) {
            /* Convert hex string to bytes */
            for (int i = 0; i < 20; i++) {
                char hex_byte[3] = {Z_STRVAL(result)[i*2], Z_STRVAL(result)[i*2+1], '\0'};
                output[i] = (unsigned char)strtol(hex_byte, NULL, 16);
            }
        }
        zval_ptr_dtor(&result);
    }

    zval_ptr_dtor(&function_name);
    zval_ptr_dtor(&params[0]);
}

/* UUID Version 5 methods */

/**
 * Generate a new UUID version 5 (name-based with SHA-1)
 *
 * Creates a UUID version 5 by hashing a namespace UUID and name using SHA-1.
 * The same namespace and name will always produce the same UUID. This is
 * preferred over version 3 due to SHA-1 being more secure than MD5.
 *
 * @param string $namespace UUID namespace as string
 * @param string $name Name to hash within the namespace
 * @return Version5 A new UUID version 5 instance
 * @throws Exception If namespace is invalid or hashing fails
 *
 * @example
 * // Generate deterministic UUID from namespace and name
 * $namespace = "6ba7b810-9dad-11d1-80b4-00c04fd430c8";
 * $uuid = Version5::generate($namespace, "example.com");
 * echo $uuid->toString(); // Always the same for these inputs
 *
 * // Preferred over Version 3 for security
 * $uuid3 = Version3::generate($namespace, "example.com");
 * $uuid5 = Version5::generate($namespace, "example.com");
 * // Different results due to different hash algorithms
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_Uuid_Version5, generate)
{
    zval *namespace_uuid;
    zend_string *name;

    ZEND_PARSE_PARAMETERS_START(2, 2)
        Z_PARAM_OBJECT_OF_CLASS(namespace_uuid, php_identifier_uuid_ce)
        Z_PARAM_STR(name)
    ZEND_PARSE_PARAMETERS_END();

    /* Get namespace UUID bytes */
    php_identifier_bit128_obj *ns_intern = PHP_IDENTIFIER_BIT128_OBJ_P(namespace_uuid);

    /* Create input for SHA-1: namespace bytes + name bytes */
    size_t input_len = 16 + ZSTR_LEN(name);
    unsigned char *input = emalloc(input_len);

    /* Copy namespace UUID bytes */
    memcpy(input, ns_intern->data, 16);

    /* Copy name bytes */
    memcpy(input + 16, ZSTR_VAL(name), ZSTR_LEN(name));

    /* Calculate SHA-1 hash */
    unsigned char hash[20]; /* SHA-1 produces 20 bytes */
    simple_sha1(input, input_len, hash);

    efree(input);

    /* Take first 16 bytes of hash for UUID */
    unsigned char uuid_bytes[16];
    memcpy(uuid_bytes, hash, 16);

    /* Set version bits: version 5 (0101) in the most significant 4 bits of byte 6 */
    uuid_bytes[6] = (uuid_bytes[6] & 0x0F) | 0x50;

    /* Set variant bits: variant 10 in the most significant 2 bits of byte 8 */
    uuid_bytes[8] = (uuid_bytes[8] & 0x3F) | 0x80;

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version5_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/**
 * Create UUID version 5 from string representation
 *
 * Parses a UUID version 5 from its standard string representation.
 * The string must be in the format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
 * and must be a valid version 5 UUID.
 *
 * @param string $uuid UUID string in standard format
 * @return Version5 A new UUID version 5 instance
 * @throws Exception If string is invalid or not version 5
 *
 * @example
 * $uuid = Version5::fromString("6ba7b810-9dad-11d1-80b4-00c04fd430c8");
 * echo $uuid->getVersion(); // 5
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_Uuid_Version5, fromString)
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

    /* Check if it's version 5 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 5) {
        zend_throw_exception(zend_ce_exception, "UUID string is not version 5", 0);
        RETURN_THROWS();
    }

    /* Create Version5 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version5_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/**
 * Create UUID version 5 from binary data
 *
 * Creates a UUID version 5 instance from exactly 16 bytes of binary data.
 * The binary data must represent a valid version 5 UUID.
 *
 * @param string $bytes Exactly 16 bytes of binary data
 * @return Version5 A new UUID version 5 instance
 * @throws Exception If bytes is not exactly 16 bytes or not version 5
 *
 * @example
 * $bytes = hex2bin("6ba7b8109dad11d180b400c04fd430c8");
 * $uuid = Version5::fromBytes($bytes);
 * echo $uuid->getVersion(); // 5
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_Uuid_Version5, fromBytes)
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

    /* Check if it's version 5 */
    int version = (uuid_bytes[6] >> 4) & 0x0F;
    if (version != 5) {
        zend_throw_exception(zend_ce_exception, "Bytes do not represent a version 5 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version5 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version5_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/**
 * Create UUID version 5 from hexadecimal string
 *
 * Creates a UUID version 5 instance from a 32-character hexadecimal string.
 * The hex string can be with or without hyphens and is case-insensitive.
 *
 * @param string $hex 32-character hexadecimal string (with or without hyphens)
 * @return Version5 A new UUID version 5 instance
 * @throws Exception If hex string is invalid or not version 5
 *
 * @example
 * $uuid = Version5::fromHex("6ba7b8109dad11d180b400c04fd430c8");
 * echo $uuid->toString(); // "6ba7b810-9dad-11d1-80b4-00c04fd430c8"
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_Uuid_Version5, fromHex)
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

    /* Check if it's version 5 */
    int version = (bytes[6] >> 4) & 0x0F;
    if (version != 5) {
        zend_throw_exception(zend_ce_exception, "Hex does not represent a version 5 UUID", 0);
        RETURN_THROWS();
    }

    /* Create Version5 UUID object */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_version5_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/* UUID Version 5 method entries */
static const zend_function_entry php_identifier_uuid_version5_methods[] = {
    PHP_ME(Identifier_Uuid_Version5, generate, arginfo_uuid_version5_generate, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_Uuid_Version5, fromString, arginfo_uuid_version5_fromString, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_Uuid_Version5, fromBytes, arginfo_uuid_version5_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_Uuid_Version5, fromHex, arginfo_uuid_version5_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_FE_END
};

/* Register UUID Version 5 class */
void php_identifier_uuid_version5_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Identifier\\Uuid", "Version5", php_identifier_uuid_version5_methods);
    php_identifier_uuid_version5_ce = zend_register_internal_class_ex(&ce, php_identifier_uuid_ce);
    php_identifier_uuid_version5_ce->ce_flags |= ZEND_ACC_FINAL;
}
