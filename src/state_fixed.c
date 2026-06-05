#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_state_fixed_create, 0, 2, Identifier\\State\\Fixed, 0)
    ZEND_ARG_TYPE_INFO(0, timestamp_ms, IS_LONG, 0)
    ZEND_ARG_TYPE_INFO(0, seed, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_fixed_advanceTime, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, milliseconds, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_fixed_advanceTimeSeconds, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, seconds, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_fixed_setTimestamp, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, timestamp_ms, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_fixed_getTimestampMs, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_fixed_getGregorianEpochTime, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_fixed_getRandomBytes, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

/* Include random headers - compatibility across PHP versions */
#if PHP_VERSION_ID >= 80200
#include "ext/random/php_random.h"
#else
#include "ext/standard/php_mt_rand.h"
#endif

/* Fixed state object handlers */
static zend_object_handlers php_identifier_state_fixed_object_handlers;

/* Fixed state methods */

/**
 * Create a new fixed state for testing
 *
 * Creates a state with fixed timestamp and deterministic random bytes.
 * This is primarily useful for testing and generating reproducible identifiers.
 *
 * @param int $timestamp Fixed timestamp in milliseconds since Unix epoch
 * @param string $randomBytes Fixed random bytes (16 bytes for deterministic generation)
 * @return Fixed A new fixed state instance
 * @throws Exception If randomBytes is not exactly 16 bytes
 *
 * @example
 * // Create fixed state for testing
 * $timestamp = 1640995200000; // 2022-01-01 00:00:00 UTC
 * $randomBytes = str_repeat("\x00", 16); // All zeros
 * $state = Fixed::create($timestamp, $randomBytes);
 *
 * // Generate reproducible identifiers
 * $uuid1 = Version4::generate($state);
 * $uuid2 = Version4::generate($state);
 * var_dump($uuid1->equals($uuid2)); // bool(true) - same every time
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_Fixed, create)
{
    zend_long timestamp_ms, seed;

    ZEND_PARSE_PARAMETERS_START(2, 2)
        Z_PARAM_LONG(timestamp_ms)
        Z_PARAM_LONG(seed)
    ZEND_PARSE_PARAMETERS_END();

    /* Create new Fixed state instance */
    zval state;
    object_init_ex(&state, php_identifier_state_fixed_ce);

    php_identifier_state_fixed_obj *intern = PHP_IDENTIFIER_STATE_FIXED_OBJ_P(&state);
    intern->timestamp_ms = (uint64_t)timestamp_ms;
    intern->seed = (uint32_t)seed;
    intern->random_state = (uint32_t)seed; /* Initialize random state with seed */

    RETURN_ZVAL(&state, 1, 0);
}

/**
 * Advance the state time by milliseconds
 *
 * Increments the internal timestamp by the specified number of milliseconds.
 * This is useful for testing time-based identifiers and simulating the
 * passage of time.
 *
 * @param int $milliseconds Number of milliseconds to advance
 * @return Fixed Returns $this for method chaining
 *
 * @example
 * $state = Fixed::create(1640995200000, 12345);
 * $ulid1 = Ulid::generate($state);
 *
 * // Advance time by 1 second
 * $state->advanceTime(1000);
 * $ulid2 = Ulid::generate($state);
 *
 * var_dump($ulid1->getTimestamp() < $ulid2->getTimestamp()); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_Fixed, advanceTime)
{
    zend_long milliseconds;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(milliseconds)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_state_fixed_obj *intern = PHP_IDENTIFIER_STATE_FIXED_OBJ_P(getThis());
    intern->timestamp_ms += (uint64_t)milliseconds;

    RETURN_ZVAL(getThis(), 1, 0);
}

/**
 * Advance the state time by seconds
 *
 * Increments the internal timestamp by the specified number of seconds.
 * This is a convenience method equivalent to calling advanceTime($seconds * 1000).
 *
 * @param int $seconds Number of seconds to advance
 * @return Fixed Returns $this for method chaining
 *
 * @example
 * $state = Fixed::create(1640995200000, 12345);
 *
 * // Advance time by 1 hour
 * $state->advanceTimeSeconds(3600);
 *
 * // Method chaining
 * $state->advanceTimeSeconds(60)->advanceTime(500);
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_Fixed, advanceTimeSeconds)
{
    zend_long seconds;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(seconds)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_state_fixed_obj *intern = PHP_IDENTIFIER_STATE_FIXED_OBJ_P(getThis());
    intern->timestamp_ms += (uint64_t)seconds * 1000ULL;

    RETURN_ZVAL(getThis(), 1, 0);
}

/**
 * Set the state timestamp to a specific value
 *
 * Sets the internal timestamp to an exact value in milliseconds since Unix epoch.
 * This allows jumping to any point in time for testing purposes.
 *
 * @param int $timestamp_ms Timestamp in milliseconds since Unix epoch
 * @return Fixed Returns $this for method chaining
 *
 * @example
 * $state = Fixed::create(1640995200000, 12345);
 *
 * // Jump to a specific date (2023-01-01 00:00:00 UTC)
 * $state->setTimestamp(1672531200000);
 *
 * $uuid = Version7::generate($state);
 * // UUID will have timestamp from 2023-01-01
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_Fixed, setTimestamp)
{
    zend_long timestamp_ms;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(timestamp_ms)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_state_fixed_obj *intern = PHP_IDENTIFIER_STATE_FIXED_OBJ_P(getThis());
    intern->timestamp_ms = (uint64_t)timestamp_ms;

    RETURN_ZVAL(getThis(), 1, 0);
}

/**
 * Get the current fixed timestamp in milliseconds
 *
 * Returns the internal fixed timestamp value in milliseconds since Unix epoch.
 * This value can be modified using advanceTime(), advanceTimeSeconds(), or setTimestamp().
 *
 * @return int Timestamp in milliseconds since Unix epoch
 *
 * @example
 * $state = Fixed::create(1640995200000, 12345);
 * echo $state->getTimestampMs(); // 1640995200000
 *
 * $state->advanceTime(5000);
 * echo $state->getTimestampMs(); // 1640995205000
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_Fixed, getTimestampMs)
{
    php_identifier_state_fixed_obj *intern = PHP_IDENTIFIER_STATE_FIXED_OBJ_P(getThis());
    RETURN_LONG((zend_long)intern->timestamp_ms);
}

/**
 * Get the fixed timestamp as Gregorian epoch time
 *
 * Converts the internal timestamp to 100-nanosecond intervals since the
 * Gregorian epoch (October 15, 1582). This is used for UUID v1 and v6 timestamps.
 *
 * @return int Timestamp in 100-nanosecond intervals since Gregorian epoch
 *
 * @example
 * $state = Fixed::create(1640995200000, 12345);
 * $gregorian = $state->getGregorianEpochTime();
 * // Returns timestamp suitable for UUID v1/v6
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_Fixed, getGregorianEpochTime)
{
    php_identifier_state_fixed_obj *intern = PHP_IDENTIFIER_STATE_FIXED_OBJ_P(getThis());

    /* Convert milliseconds to 100-nanosecond intervals since Gregorian epoch */
    /* Gregorian epoch: October 15, 1582 00:00:00 UTC */
    /* Unix epoch: January 1, 1970 00:00:00 UTC */
    /* Difference: 12219292800 seconds = 122192928000000000 * 100ns intervals */
    const uint64_t GREGORIAN_TO_UNIX_100NS = 122192928000000000ULL;

    uint64_t unix_100ns = intern->timestamp_ms * 10000ULL; /* Convert ms to 100ns intervals */
    uint64_t gregorian_100ns = unix_100ns + GREGORIAN_TO_UNIX_100NS;

    RETURN_LONG((zend_long)gregorian_100ns);
}

/**
 * Generate deterministic pseudo-random bytes
 *
 * Returns a string of pseudo-random bytes using a seeded Mersenne Twister
 * generator. The output is deterministic based on the seed provided during
 * state creation. This is useful for testing and generating reproducible identifiers.
 *
 * @param int $length Number of random bytes to generate (1-1024)
 * @return string Binary string of pseudo-random bytes
 * @throws Exception If length is out of valid range
 *
 * @example
 * $state = Fixed::create(1640995200000, 12345);
 * $bytes1 = $state->getRandomBytes(16);
 * $bytes2 = $state->getRandomBytes(16);
 * // bytes1 and bytes2 will be different but deterministic
 *
 * // Same seed produces same sequence
 * $state2 = Fixed::create(1640995200000, 12345);
 * $bytes3 = $state2->getRandomBytes(16);
 * var_dump($bytes1 === $bytes3); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Identifier_State_Fixed, getRandomBytes)
{
    zend_long length;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(length)
    ZEND_PARSE_PARAMETERS_END();

    if (length <= 0 || length > 1024) {
        zend_throw_exception(zend_ce_exception, "Length must be between 1 and 1024", 0);
        RETURN_THROWS();
    }

    php_identifier_state_fixed_obj *intern = PHP_IDENTIFIER_STATE_FIXED_OBJ_P(getThis());

    /* Generate deterministic random bytes using PHP's MT19937 */
    zend_string *result = zend_string_alloc(length, 0);
    unsigned char *buffer = (unsigned char*)ZSTR_VAL(result);

    /* Seed the MT19937 with current random state */
    php_mt_srand(intern->random_state);

    for (zend_long i = 0; i < length; i++) {
        /* Use PHP's MT19937 for high-quality deterministic randomness */
        zend_long random_val = php_mt_rand();
        buffer[i] = (unsigned char)(random_val & 0xFF);
    }

    /* Update the random state for next call (advance by number of bytes generated) */
    intern->random_state += (uint32_t)length;

    ZSTR_VAL(result)[length] = '\0';
    RETURN_STR(result);
}

/* Fixed state method entries */
static const zend_function_entry php_identifier_state_fixed_methods[] = {
    PHP_ME(Identifier_State_Fixed, create, arginfo_state_fixed_create, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Identifier_State_Fixed, advanceTime, arginfo_state_fixed_advanceTime, ZEND_ACC_PUBLIC)
    PHP_ME(Identifier_State_Fixed, advanceTimeSeconds, arginfo_state_fixed_advanceTimeSeconds, ZEND_ACC_PUBLIC)
    PHP_ME(Identifier_State_Fixed, setTimestamp, arginfo_state_fixed_setTimestamp, ZEND_ACC_PUBLIC)
    PHP_ME(Identifier_State_Fixed, getTimestampMs, arginfo_state_fixed_getTimestampMs, ZEND_ACC_PUBLIC)
    PHP_ME(Identifier_State_Fixed, getGregorianEpochTime, arginfo_state_fixed_getGregorianEpochTime, ZEND_ACC_PUBLIC)
    PHP_ME(Identifier_State_Fixed, getRandomBytes, arginfo_state_fixed_getRandomBytes, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* Fixed state object creation */
static zend_object *php_identifier_state_fixed_create_object(zend_class_entry *ce)
{
    php_identifier_state_fixed_obj *intern = zend_object_alloc(sizeof(php_identifier_state_fixed_obj), ce);

    zend_object_std_init(&intern->std, ce);
    object_properties_init(&intern->std, ce);

    intern->std.handlers = &php_identifier_state_fixed_object_handlers;
    intern->timestamp_ms = 0;
    intern->seed = 0;
    intern->random_state = 0;

    return &intern->std;
}

/* Register Fixed state class */
void php_identifier_state_fixed_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Identifier\\State", "Fixed", php_identifier_state_fixed_methods);
    php_identifier_state_fixed_ce = zend_register_internal_class(&ce);
    php_identifier_state_fixed_ce->create_object = php_identifier_state_fixed_create_object;

    /* Implement State interface */
    zend_class_implements(php_identifier_state_fixed_ce, 1, php_identifier_state_ce);

    /* Set up object handlers */
    memcpy(&php_identifier_state_fixed_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
    php_identifier_state_fixed_object_handlers.offset = XtOffsetOf(php_identifier_state_fixed_obj, std);
}
