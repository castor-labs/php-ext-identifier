#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_state_system_getInstance, 0, 0, Identifier\\State\\System, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_system_getTimestampMs, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_system_getGregorianEpochTime, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_system_getRandomBytes, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

/* Include random headers - compatibility across PHP versions */
#if PHP_VERSION_ID >= 80200
#include "ext/random/php_random.h"
#else
#include "ext/standard/php_random.h"
#endif

/* System state object handlers */
static zend_object_handlers php_identifier_state_system_object_handlers;

/* Singleton instance */
static zval system_state_singleton;
static bool system_state_initialized = false;

/* System state methods */

/**
 * Get the singleton system state instance
 *
 * Returns the shared system state that uses real system time and
 * cryptographically secure random number generation. This is the
 * default state used when no state is specified.
 *
 * @return System The singleton system state instance
 *
 * @example
 * $state = System::getInstance();
 * $uuid = Version4::generate($state);
 * $ulid = Ulid::generate($state);
 *
 * // Same instance every time
 * $state2 = System::getInstance();
 * var_dump($state === $state2); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_System, getInstance)
{
    if (!system_state_initialized) {
        /* Create singleton instance */
        object_init_ex(&system_state_singleton, php_identifier_state_system_ce);
        system_state_initialized = true;

        /* Make sure it doesn't get garbage collected */
        Z_ADDREF(system_state_singleton);
    }

    RETURN_ZVAL(&system_state_singleton, 1, 0);
}

/**
 * Get the current system time in milliseconds
 *
 * Returns the current Unix timestamp in milliseconds. This is used for
 * generating time-based identifiers like UUIDs v1, v6, v7 and ULIDs.
 *
 * @return int Current timestamp in milliseconds since Unix epoch
 *
 * @example
 * $state = System::getInstance();
 * $timestamp = $state->getTimestampMs();
 * echo date('Y-m-d H:i:s.', $timestamp / 1000) . ($timestamp % 1000);
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_System, getTimestampMs)
{
    RETURN_LONG(php_identifier_get_timestamp_ms());
}

/**
 * Get the current time as Gregorian epoch time
 *
 * Returns the current time in 100-nanosecond intervals since the Gregorian
 * epoch (October 15, 1582). This is used for UUID v1 and v6 timestamps.
 *
 * @return int Timestamp in 100-nanosecond intervals since Gregorian epoch
 *
 * @example
 * $state = System::getInstance();
 * $gregorian = $state->getGregorianEpochTime();
 * // Convert back to Unix timestamp
 * $unix_ns = ($gregorian - 122192928000000000) * 100;
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_System, getGregorianEpochTime)
{
    RETURN_LONG(php_identifier_get_gregorian_epoch_time());
}

/**
 * Generate cryptographically secure random bytes
 *
 * Returns a string of random bytes using the system's cryptographically
 * secure random number generator (CSPRNG). This is used for generating
 * random components of identifiers.
 *
 * @param int $length Number of random bytes to generate (1-1024)
 * @return string Binary string of random bytes
 * @throws Exception If length is out of valid range
 *
 * @example
 * $state = System::getInstance();
 * $randomBytes = $state->getRandomBytes(16);
 * echo bin2hex($randomBytes); // 32-character hex string
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_System, getRandomBytes)
{
    zend_long length;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(length)
    ZEND_PARSE_PARAMETERS_END();

    if (length <= 0 || length > 1024) {
        zend_throw_exception(zend_ce_exception, "Length must be between 1 and 1024", 0);
        RETURN_THROWS();
    }

    /* Use PHP's secure random_bytes function directly */
    zend_string *result = zend_string_alloc(length, 0);

    if (php_random_bytes(ZSTR_VAL(result), length, 1) == SUCCESS) {
        ZSTR_VAL(result)[length] = '\0';
        RETURN_STR(result);
    }

    /* Fallback to utility function if direct call fails */
    php_identifier_generate_random_bytes((unsigned char*)ZSTR_VAL(result), length);
    ZSTR_VAL(result)[length] = '\0';

    RETURN_STR(result);
}

/* System state method entries */
static const zend_function_entry php_identifier_state_system_methods[] = {
    PHP_ME(Identifier_State_System, getInstance, arginfo_state_system_getInstance, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_State_System, getTimestampMs, arginfo_state_system_getTimestampMs, ZEND_ACC_PUBLIC)
    PHP_ME(Identifier_State_System, getGregorianEpochTime, arginfo_state_system_getGregorianEpochTime, ZEND_ACC_PUBLIC)
    PHP_ME(Identifier_State_System, getRandomBytes, arginfo_state_system_getRandomBytes, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* System state object creation */
static zend_object *php_identifier_state_system_create_object(zend_class_entry *ce)
{
    php_identifier_state_system_obj *intern = zend_object_alloc(sizeof(php_identifier_state_system_obj), ce);

    zend_object_std_init(&intern->std, ce);
    object_properties_init(&intern->std, ce);

    intern->std.handlers = &php_identifier_state_system_object_handlers;

    return &intern->std;
}

/* Register System state class */
void php_identifier_state_system_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Identifier\\State", "System", php_identifier_state_system_methods);
    php_identifier_state_system_ce = zend_register_internal_class(&ce);
    php_identifier_state_system_ce->create_object = php_identifier_state_system_create_object;

    /* Implement State interface */
    zend_class_implements(php_identifier_state_system_ce, 1, php_identifier_state_ce);

    /* Set up object handlers */
    memcpy(&php_identifier_state_system_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
    php_identifier_state_system_object_handlers.offset = XtOffsetOf(php_identifier_state_system_obj, std);
}
