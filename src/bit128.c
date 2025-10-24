#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"
#include <string.h>
#include <ctype.h>

/* Arginfo declarations */
ZEND_BEGIN_ARG_INFO_EX(arginfo_bit128_construct, 0, 0, 1)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_bit128_getBytes, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_bit128_toBytes, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_bit128_equals, 0, 1, _IS_BOOL, 0)
    ZEND_ARG_OBJ_INFO(0, other, Php\\Identifier\\Bit128, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_bit128_compare, 0, 1, IS_LONG, 0)
    ZEND_ARG_OBJ_INFO(0, other, Php\\Identifier\\Bit128, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_bit128_toHex, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_bit128_fromHex, 0, 1, Php\\Identifier\\Bit128, 0)
    ZEND_ARG_TYPE_INFO(0, hex, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_bit128_fromBytes, 0, 1, Php\\Identifier\\Bit128, 0)
    ZEND_ARG_TYPE_INFO(0, bytes, IS_STRING, 0)
ZEND_END_ARG_INFO()

/* Bit128 object handlers */
static zend_object_handlers php_identifier_bit128_object_handlers;

/* Bit128 methods */

/**
 * Create a new 128-bit identifier from bytes
 *
 * Constructs a new Bit128 instance from exactly 16 bytes of binary data.
 * This is the base class for all 128-bit identifiers in this extension.
 *
 * @param string $bytes Exactly 16 bytes of binary data
 * @throws Exception If bytes is not exactly 16 bytes long
 *
 * @example
 * $bytes = random_bytes(16);
 * $bit128 = new Bit128($bytes);
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, __construct)
{
    zend_string *bytes;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(bytes)
    ZEND_PARSE_PARAMETERS_END();

    if (ZSTR_LEN(bytes) != 16) {
        zend_throw_exception(zend_ce_exception, "Bytes must be exactly 16 bytes long", 0);
        RETURN_THROWS();
    }

    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    memcpy(intern->data, ZSTR_VAL(bytes), 16);
}

/**
 * Get the raw 16-byte binary representation
 *
 * Returns the internal 16-byte binary data that represents this 128-bit identifier.
 * This is the most compact representation and is useful for storage and transmission.
 *
 * @return string The 16-byte binary representation
 *
 * @example
 * $id = new Bit128(random_bytes(16));
 * $bytes = $id->getBytes();
 * echo strlen($bytes); // 16
 * echo bin2hex($bytes); // hex representation
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, getBytes)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    RETURN_STRINGL((char*)intern->data, 16);
}

/**
 * Alias for getBytes() method
 *
 * This method is an alias for getBytes() and returns the same 16-byte binary data.
 * Provided for API consistency and convenience.
 *
 * @return string The 16-byte binary representation
 *
 * @example
 * $id = new Bit128(random_bytes(16));
 * $bytes1 = $id->getBytes();
 * $bytes2 = $id->toBytes();
 * var_dump($bytes1 === $bytes2); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, toBytes)
{
    /* Alias for getBytes */
    PHP_MN(Php_Identifier_Bit128_getBytes)(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

/**
 * Check if this identifier equals another
 *
 * Performs a byte-by-byte comparison to determine if two 128-bit
 * identifiers contain exactly the same data.
 *
 * @param Bit128 $other The identifier to compare against
 * @return bool True if the identifiers are equal, false otherwise
 *
 * @example
 * $id1 = Bit128::fromHex('550e8400e29b41d4a716446655440000');
 * $id2 = Bit128::fromHex('550e8400e29b41d4a716446655440000');
 * var_dump($id1->equals($id2)); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, equals)
{
    zval *other;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_OBJECT_OF_CLASS(other, php_identifier_bit128_ce)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    php_identifier_bit128_obj *other_intern = PHP_IDENTIFIER_BIT128_OBJ_P(other);

    RETURN_BOOL(memcmp(intern->data, other_intern->data, 16) == 0);
}

/**
 * Compare this identifier with another for ordering
 *
 * Performs a lexicographic comparison of the binary data to determine
 * the relative ordering of two 128-bit identifiers. Useful for sorting.
 *
 * @param Bit128 $other The identifier to compare against
 * @return int -1 if this < other, 0 if equal, 1 if this > other
 *
 * @example
 * $id1 = Bit128::fromHex('550e8400e29b41d4a716446655440000');
 * $id2 = Bit128::fromHex('550e8400e29b41d4a716446655440001');
 * echo $id1->compare($id2); // -1 (id1 < id2)
 * echo $id2->compare($id1); // 1 (id2 > id1)
 * echo $id1->compare($id1); // 0 (equal)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, compare)
{
    zval *other;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_OBJECT_OF_CLASS(other, php_identifier_bit128_ce)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    php_identifier_bit128_obj *other_intern = PHP_IDENTIFIER_BIT128_OBJ_P(other);

    int result = memcmp(intern->data, other_intern->data, 16);
    RETURN_LONG(result < 0 ? -1 : (result > 0 ? 1 : 0));
}

/**
 * Convert the identifier to a hexadecimal string
 *
 * Returns a 32-character lowercase hexadecimal representation of the
 * 128-bit identifier. This is useful for debugging and storage.
 *
 * @return string 32-character hexadecimal string (lowercase)
 *
 * @example
 * $id = Bit128::fromHex('550e8400e29b41d4a716446655440000');
 * echo $id->toHex(); // "550e8400e29b41d4a716446655440000"
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, toHex)
{
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(getThis());
    
    zend_string *result = zend_string_alloc(32, 0);
    char *hex = ZSTR_VAL(result);
    
    for (int i = 0; i < 16; i++) {
        sprintf(hex + (i * 2), "%02x", intern->data[i]);
    }
    
    ZSTR_VAL(result)[32] = '\0';
    RETURN_STR(result);
}

/**
 * Create a new identifier from a hexadecimal string
 *
 * Parses a 32-character hexadecimal string (with or without dashes)
 * and creates a new Bit128 instance. The hex string is case-insensitive.
 *
 * @param string $hex 32-character hexadecimal string (case-insensitive)
 * @return Bit128 New identifier instance
 * @throws Exception If hex string is invalid or wrong length
 *
 * @example
 * $id = Bit128::fromHex('550e8400e29b41d4a716446655440000');
 * $same = Bit128::fromHex('550E8400-E29B-41D4-A716-446655440000');
 * var_dump($id->equals($same)); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, fromHex)
{
    zend_string *hex;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(hex)
    ZEND_PARSE_PARAMETERS_END();

    /* Validate hex string length */
    if (ZSTR_LEN(hex) != 32) {
        zend_throw_exception(zend_ce_exception, "Hex string must be exactly 32 characters long", 0);
        RETURN_THROWS();
    }

    /* Validate hex characters */
    const char *hex_str = ZSTR_VAL(hex);
    for (int i = 0; i < 32; i++) {
        if (!isxdigit(hex_str[i])) {
            zend_throw_exception(zend_ce_exception, "Invalid hex character in string", 0);
            RETURN_THROWS();
        }
    }

    /* Parse hex string to bytes */
    unsigned char bytes[16];
    for (int i = 0; i < 16; i++) {
        char hex_byte[3] = {hex_str[i * 2], hex_str[i * 2 + 1], '\0'};
        bytes[i] = (unsigned char)strtol(hex_byte, NULL, 16);
    }

    /* Create new Bit128 object */
    object_init_ex(return_value, php_identifier_bit128_ce);
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(return_value);
    memcpy(intern->data, bytes, 16);
}

/**
 * Create a new identifier from binary data
 *
 * Creates a new Bit128 instance from exactly 16 bytes of binary data.
 * This is useful when reading identifiers from binary storage or network protocols.
 *
 * @param string $bytes Exactly 16 bytes of binary data
 * @return Bit128 New identifier instance
 * @throws Exception If bytes is not exactly 16 bytes long
 *
 * @example
 * $bytes = random_bytes(16);
 * $id = Bit128::fromBytes($bytes);
 * var_dump($id->getBytes() === $bytes); // bool(true)
 *
 * // From hex string converted to bytes
 * $hex = '550e8400e29b41d4a716446655440000';
 * $bytes = hex2bin($hex);
 * $id = Bit128::fromBytes($bytes);
 * echo $id->toHex(); // "550e8400e29b41d4a716446655440000"
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Bit128, fromBytes)
{
    zend_string *bytes;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(bytes)
    ZEND_PARSE_PARAMETERS_END();

    /* Validate bytes length */
    if (ZSTR_LEN(bytes) != 16) {
        zend_throw_exception(zend_ce_exception, "Bytes must be exactly 16 bytes long", 0);
        RETURN_THROWS();
    }

    /* Create new Bit128 object */
    object_init_ex(return_value, php_identifier_bit128_ce);
    php_identifier_bit128_obj *intern = PHP_IDENTIFIER_BIT128_OBJ_P(return_value);
    memcpy(intern->data, ZSTR_VAL(bytes), 16);
}

/* Bit128 method entries */
static const zend_function_entry php_identifier_bit128_methods[] = {
    PHP_ME(Php_Identifier_Bit128, __construct, arginfo_bit128_construct, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Bit128, getBytes, arginfo_bit128_getBytes, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Bit128, toBytes, arginfo_bit128_toBytes, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Bit128, equals, arginfo_bit128_equals, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Bit128, compare, arginfo_bit128_compare, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Bit128, toHex, arginfo_bit128_toHex, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Bit128, fromHex, arginfo_bit128_fromHex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Bit128, fromBytes, arginfo_bit128_fromBytes, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_FE_END
};

/* Bit128 object creation */
static zend_object *php_identifier_bit128_create_object(zend_class_entry *ce)
{
    php_identifier_bit128_obj *intern = zend_object_alloc(sizeof(php_identifier_bit128_obj), ce);

    zend_object_std_init(&intern->std, ce);
    object_properties_init(&intern->std, ce);

    intern->std.handlers = &php_identifier_bit128_object_handlers;
    memset(intern->data, 0, 16);

    return &intern->std;
}

/* Register Bit128 class */
void php_identifier_bit128_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier", "Bit128", php_identifier_bit128_methods);
    php_identifier_bit128_ce = zend_register_internal_class(&ce);
    php_identifier_bit128_ce->create_object = php_identifier_bit128_create_object;

    /* Set up object handlers */
    memcpy(&php_identifier_bit128_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
}
