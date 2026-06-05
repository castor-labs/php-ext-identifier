#include "php.h"
#include "php_identifier.h"

/**
 * State interface for identifier generation
 *
 * Defines the interface for controlling time and randomness sources during
 * identifier generation. States allow you to customize how timestamps and
 * random bytes are generated, which is particularly useful for testing
 * with deterministic values or for using alternative time sources.
 *
 * Two implementations are provided:
 * - State\System - Uses real system time and cryptographically secure randomness
 * - State\Fixed - Uses fixed/deterministic values for reproducible testing
 *
 * @see State\System For production use with real time and randomness
 * @see State\Fixed For testing with deterministic values
 * @since 1.0.0
 */

/* Arginfo for interface methods */
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_getTimestampMs, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_getGregorianEpochTime, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state_getRandomBytes, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

/* State interface methods */
static const zend_function_entry php_identifier_state_methods[] = {
    /**
     * Get the current timestamp in milliseconds
     *
     * Returns the timestamp in milliseconds since Unix epoch (January 1, 1970).
     * This is used for generating time-based identifiers like UUIDs v7 and ULIDs.
     *
     * @return int Timestamp in milliseconds since Unix epoch
     * @since 1.0.0
     */
    ZEND_ABSTRACT_ME_WITH_FLAGS(Identifier_State, getTimestampMs, arginfo_state_getTimestampMs, ZEND_ACC_PUBLIC | ZEND_ACC_ABSTRACT)
    /**
     * Get the current time as Gregorian epoch time
     *
     * Returns the current time in 100-nanosecond intervals since the Gregorian
     * epoch (October 15, 1582). This is used for UUID v1 and v6 timestamps.
     *
     * @return int Timestamp in 100-nanosecond intervals since Gregorian epoch
     * @since 1.0.0
     */
    ZEND_ABSTRACT_ME_WITH_FLAGS(Identifier_State, getGregorianEpochTime, arginfo_state_getGregorianEpochTime, ZEND_ACC_PUBLIC | ZEND_ACC_ABSTRACT)
    /**
     * Generate random bytes
     *
     * Returns a string of random bytes. The implementation determines whether
     * these are cryptographically secure (System) or deterministic (Fixed).
     *
     * @param int $length Number of random bytes to generate (1-1024)
     * @return string Binary string of random bytes
     * @throws Exception If length is out of valid range
     * @since 1.0.0
     */
    ZEND_ABSTRACT_ME_WITH_FLAGS(Identifier_State, getRandomBytes, arginfo_state_getRandomBytes, ZEND_ACC_PUBLIC | ZEND_ACC_ABSTRACT)
    PHP_FE_END
};

/* Forward declarations */
void php_identifier_state_system_register_class(void);
void php_identifier_state_fixed_register_class(void);

/* Register State interface and implementations */
void php_identifier_state_register_classes(void)
{
    zend_class_entry ce;

    /* Register State interface */
    INIT_NS_CLASS_ENTRY(ce, "Identifier", "State", php_identifier_state_methods);
    php_identifier_state_ce = zend_register_internal_interface(&ce);

    /* Interface methods are defined by implementing classes */
    /* No need to register method signatures for interfaces in PHP extensions */
    /* The interface contract is enforced at runtime */

    /* Register implementations */
    php_identifier_state_system_register_class();
    php_identifier_state_fixed_register_class();
}
