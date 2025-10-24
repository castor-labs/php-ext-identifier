#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <ctype.h>
#include <string.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_getVersion, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_getVariant, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_toString, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_fromString, 0, 1, Php\\Identifier\\Uuid, 0)
    ZEND_ARG_TYPE_INFO(0, uuid, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_fromBytes, 0, 1, Php\\Identifier\\Uuid, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_fromHex, 0, 1, Php\\Identifier\\Uuid, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_isNil, 0, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_nil, 0, 0, Php\\Identifier\\Uuid, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_uuid_isMax, 0, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_uuid_max, 0, 0, Php\\Identifier\\Uuid, 0)
ZEND_END_ARG_INFO()

/* UUID methods */

/**
 * Get the UUID version number
 *
 * Returns the version number stored in bits 12-15 of the time_hi_and_version field.
 * This indicates which UUID generation algorithm was used.
 *
 * @return int The UUID version (1, 3, 4, 5, 6, or 7)
 *
 * @example
 * $uuid = Version4::generate();
 * echo $uuid->getVersion(); // 4
 *
 * $uuid = Version1::generate();
 * echo $uuid->getVersion(); // 1
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid, getVersion)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    
    /* Version is stored in bits 12-15 of the time_hi_and_version field (byte 6) */
    int version = (intern->data[6] >> 4) & 0x0F;
    RETURN_LONG(version);
}

/**
 * Get the UUID variant
 *
 * Returns the variant field which indicates the layout of the UUID.
 * For RFC 4122 UUIDs, this should always be 2 (binary 10).
 *
 * @return int The UUID variant (typically 2 for RFC 4122)
 *
 * @example
 * $uuid = Version4::generate();
 * echo $uuid->getVariant(); // 2 (RFC 4122 variant)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid, getVariant)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    
    /* Variant is stored in the most significant bits of byte 8 */
    int variant = (intern->data[8] >> 6) & 0x03;
    RETURN_LONG(variant);
}

/**
 * Convert UUID to standard string representation
 *
 * Returns the UUID in the standard 8-4-4-4-12 hexadecimal format with hyphens.
 * This is the canonical string representation defined by RFC 4122.
 *
 * @return string UUID in format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
 *
 * @example
 * $uuid = Version4::generate();
 * echo $uuid->toString(); // "f47ac10b-58cc-4372-a567-0e02b2c3d479"
 *
 * // Can also use string casting
 * echo (string) $uuid; // Same result
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid, toString)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    
    zend_string *result = zend_string_alloc(36, 0);
    char *str = ZSTR_VAL(result);
    
    sprintf(str, "%02x%02x%02x%02x-%02x%02x-%02x%02x-%02x%02x-%02x%02x%02x%02x%02x%02x",
        intern->data[0], intern->data[1], intern->data[2], intern->data[3],
        intern->data[4], intern->data[5],
        intern->data[6], intern->data[7],
        intern->data[8], intern->data[9],
        intern->data[10], intern->data[11], intern->data[12], intern->data[13], intern->data[14], intern->data[15]
    );
    
    RETURN_STR(result);
}

/**
 * Magic method for string conversion
 *
 * Allows the UUID to be automatically converted to a string when used in
 * string contexts. Delegates to the toString() method.
 *
 * @return string UUID in standard format
 *
 * @example
 * $uuid = Version4::generate();
 * echo $uuid; // Automatically calls __toString()
 * echo "UUID: $uuid"; // String interpolation
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Uuid, __toString)
{
    /* Delegate to toString */
    PHP_MN(Php_Identifier_Uuid_toString)(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

static PHP_METHOD(Php_Identifier_Uuid, fromString)
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

    /* Extract version from byte 6 (upper nibble) */
    int version = (uuid_bytes[6] >> 4) & 0x0F;

    /* Determine which class to instantiate based on version */
    zend_class_entry *target_ce;
    switch (version) {
        case 1:
            target_ce = php_identifier_uuid_version1_ce;
            break;
        case 3:
            target_ce = php_identifier_uuid_version3_ce;
            break;
        case 4:
            target_ce = php_identifier_uuid_version4_ce;
            break;
        case 5:
            target_ce = php_identifier_uuid_version5_ce;
            break;
        case 6:
            target_ce = php_identifier_uuid_version6_ce;
            break;
        case 7:
            target_ce = php_identifier_uuid_version7_ce;
            break;
        default:
            /* Unknown version, use generic Uuid class */
            target_ce = php_identifier_uuid_ce;
            break;
    }

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, target_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid, fromBytes)
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

    /* Extract version from byte 6 (upper nibble) */
    int version = (uuid_bytes[6] >> 4) & 0x0F;

    /* Determine which class to instantiate based on version */
    zend_class_entry *target_ce;
    switch (version) {
        case 1:
            target_ce = php_identifier_uuid_version1_ce;
            break;
        case 3:
            target_ce = php_identifier_uuid_version3_ce;
            break;
        case 4:
            target_ce = php_identifier_uuid_version4_ce;
            break;
        case 5:
            target_ce = php_identifier_uuid_version5_ce;
            break;
        case 6:
            target_ce = php_identifier_uuid_version6_ce;
            break;
        case 7:
            target_ce = php_identifier_uuid_version7_ce;
            break;
        default:
            /* Unknown version, use generic Uuid class */
            target_ce = php_identifier_uuid_ce;
            break;
    }

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, target_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, uuid_bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid, fromHex)
{
    zend_string *hex;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(hex)
    ZEND_PARSE_PARAMETERS_END();

    /* Convert hex to bytes first, then delegate to fromBytes */
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

    /* Extract version from byte 6 (upper nibble) */
    int version = (bytes[6] >> 4) & 0x0F;

    /* Determine which class to instantiate based on version */
    zend_class_entry *target_ce;
    switch (version) {
        case 1:
            target_ce = php_identifier_uuid_version1_ce;
            break;
        case 3:
            target_ce = php_identifier_uuid_version3_ce;
            break;
        case 4:
            target_ce = php_identifier_uuid_version4_ce;
            break;
        case 5:
            target_ce = php_identifier_uuid_version5_ce;
            break;
        case 6:
            target_ce = php_identifier_uuid_version6_ce;
            break;
        case 7:
            target_ce = php_identifier_uuid_version7_ce;
            break;
        default:
            /* Unknown version, use generic Uuid class */
            target_ce = php_identifier_uuid_ce;
            break;
    }

    /* Create UUID object */
    zval uuid;
    object_init_ex(&uuid, target_ce);

    /* Set the UUID bytes */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memcpy(intern->data, bytes, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid, isNil)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    
    for (int i = 0; i < 16; i++) {
        if (intern->data[i] != 0) {
            RETURN_FALSE;
        }
    }
    RETURN_TRUE;
}

static PHP_METHOD(Php_Identifier_Uuid, nil)
{
    /* Create nil UUID (all zeros) */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_ce);

    /* Get the internal object and set all bytes to 0 */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memset(intern->data, 0, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

static PHP_METHOD(Php_Identifier_Uuid, isMax)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    
    for (int i = 0; i < 16; i++) {
        if (intern->data[i] != 0xFF) {
            RETURN_FALSE;
        }
    }
    RETURN_TRUE;
}

static PHP_METHOD(Php_Identifier_Uuid, max)
{
    /* Create max UUID (all ones) */
    zval uuid;
    object_init_ex(&uuid, php_identifier_uuid_ce);

    /* Get the internal object and set all bytes to 0xFF */
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(&uuid);
    memset(intern->data, 0xFF, 16);

    RETURN_ZVAL(&uuid, 1, 0);
}

/* UUID method entries */
static const zend_function_entry php_identifier_uuid_methods[] = {
    PHP_ME(Php_Identifier_Uuid, getVersion, arginfo_uuid_getVersion, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid, getVariant, arginfo_uuid_getVariant, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid, toString, arginfo_uuid_toString, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid, __toString, arginfo_uuid_toString, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid, fromString, arginfo_uuid_fromString, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid, fromBytes, arginfo_uuid_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid, fromHex, arginfo_uuid_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid, isNil, arginfo_uuid_isNil, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid, nil, arginfo_uuid_nil, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Uuid, isMax, arginfo_uuid_isMax, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Uuid, max, arginfo_uuid_max, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_FE_END
};

/* Forward declarations for UUID version classes */
void php_identifier_uuid_version1_register_class(void);
void php_identifier_uuid_version3_register_class(void);
void php_identifier_uuid_version4_register_class(void);
void php_identifier_uuid_version5_register_class(void);
void php_identifier_uuid_version6_register_class(void);
void php_identifier_uuid_version7_register_class(void);

/* Register UUID classes */
void php_identifier_uuid_register_classes(void)
{
    zend_class_entry ce;

    /* Register base UUID class */
    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier", "Uuid", php_identifier_uuid_methods);
    php_identifier_uuid_ce = zend_register_internal_class_ex(&ce, php_identifier_bit128_ce);

    /* Register all UUID version classes */
    php_identifier_uuid_version1_register_class();
    php_identifier_uuid_version3_register_class();
    php_identifier_uuid_version4_register_class();
    php_identifier_uuid_version5_register_class();
    php_identifier_uuid_version6_register_class();
    php_identifier_uuid_version7_register_class();
}
