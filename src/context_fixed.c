#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_context_fixed_create, 0, 2, Php\\Identifier\\Context\\Fixed, 0)
    ZEND_ARG_TYPE_INFO(0, timestamp_ms, IS_LONG, 0)
    ZEND_ARG_TYPE_INFO(0, seed, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_fixed_advanceTime, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, milliseconds, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_fixed_advanceTimeSeconds, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, seconds, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_fixed_setTimestamp, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, timestamp_ms, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_fixed_getTimestampMs, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_fixed_getGregorianEpochTime, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_fixed_getRandomBytes, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

/* Include MT19937 header - compatibility across PHP versions */
#if PHP_VERSION_ID >= 80200
#include "ext/random/php_random.h"
#else
#include "ext/standard/php_mt_rand.h"
#endif

/* Fixed context object handlers */
static zend_object_handlers php_identifier_context_fixed_object_handlers;

/* Fixed context methods */
static PHP_METHOD(Php_Identifier_Context_Fixed, create)
{
    zend_long timestamp_ms, seed;

    ZEND_PARSE_PARAMETERS_START(2, 2)
        Z_PARAM_LONG(timestamp_ms)
        Z_PARAM_LONG(seed)
    ZEND_PARSE_PARAMETERS_END();

    /* Create new Fixed context instance */
    zval context;
    object_init_ex(&context, php_identifier_context_fixed_ce);

    php_identifier_context_fixed_obj *intern = PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(&context);
    intern->timestamp_ms = (uint64_t)timestamp_ms;
    intern->seed = (uint32_t)seed;
    intern->random_state = (uint32_t)seed; /* Initialize random state with seed */

    RETURN_ZVAL(&context, 1, 0);
}

static PHP_METHOD(Php_Identifier_Context_Fixed, advanceTime)
{
    zend_long milliseconds;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(milliseconds)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_context_fixed_obj *intern = PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(getThis());
    intern->timestamp_ms += (uint64_t)milliseconds;

    RETURN_ZVAL(getThis(), 1, 0);
}

static PHP_METHOD(Php_Identifier_Context_Fixed, advanceTimeSeconds)
{
    zend_long seconds;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(seconds)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_context_fixed_obj *intern = PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(getThis());
    intern->timestamp_ms += (uint64_t)seconds * 1000ULL;

    RETURN_ZVAL(getThis(), 1, 0);
}

static PHP_METHOD(Php_Identifier_Context_Fixed, setTimestamp)
{
    zend_long timestamp_ms;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(timestamp_ms)
    ZEND_PARSE_PARAMETERS_END();

    php_identifier_context_fixed_obj *intern = PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(getThis());
    intern->timestamp_ms = (uint64_t)timestamp_ms;

    RETURN_ZVAL(getThis(), 1, 0);
}

static PHP_METHOD(Php_Identifier_Context_Fixed, getTimestampMs)
{
    php_identifier_context_fixed_obj *intern = PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(getThis());
    RETURN_LONG((zend_long)intern->timestamp_ms);
}

static PHP_METHOD(Php_Identifier_Context_Fixed, getGregorianEpochTime)
{
    php_identifier_context_fixed_obj *intern = PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(getThis());

    /* Convert milliseconds to 100-nanosecond intervals since Gregorian epoch */
    /* Gregorian epoch: October 15, 1582 00:00:00 UTC */
    /* Unix epoch: January 1, 1970 00:00:00 UTC */
    /* Difference: 12219292800 seconds = 122192928000000000 * 100ns intervals */
    const uint64_t GREGORIAN_TO_UNIX_100NS = 122192928000000000ULL;

    uint64_t unix_100ns = intern->timestamp_ms * 10000ULL; /* Convert ms to 100ns intervals */
    uint64_t gregorian_100ns = unix_100ns + GREGORIAN_TO_UNIX_100NS;

    RETURN_LONG((zend_long)gregorian_100ns);
}

static PHP_METHOD(Php_Identifier_Context_Fixed, getRandomBytes)
{
    zend_long length;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(length)
    ZEND_PARSE_PARAMETERS_END();

    if (length <= 0 || length > 1024) {
        zend_throw_exception(zend_ce_exception, "Length must be between 1 and 1024", 0);
        RETURN_THROWS();
    }

    php_identifier_context_fixed_obj *intern = PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(getThis());

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

/* Fixed context method entries */
static const zend_function_entry php_identifier_context_fixed_methods[] = {
    PHP_ME(Php_Identifier_Context_Fixed, create, arginfo_context_fixed_create, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Context_Fixed, advanceTime, arginfo_context_fixed_advanceTime, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Context_Fixed, advanceTimeSeconds, arginfo_context_fixed_advanceTimeSeconds, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Context_Fixed, setTimestamp, arginfo_context_fixed_setTimestamp, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Context_Fixed, getTimestampMs, arginfo_context_fixed_getTimestampMs, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Context_Fixed, getGregorianEpochTime, arginfo_context_fixed_getGregorianEpochTime, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Context_Fixed, getRandomBytes, arginfo_context_fixed_getRandomBytes, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* Fixed context object creation */
static zend_object *php_identifier_context_fixed_create_object(zend_class_entry *ce)
{
    php_identifier_context_fixed_obj *intern = zend_object_alloc(sizeof(php_identifier_context_fixed_obj), ce);

    zend_object_std_init(&intern->std, ce);
    object_properties_init(&intern->std, ce);

    intern->std.handlers = &php_identifier_context_fixed_object_handlers;
    intern->timestamp_ms = 0;
    intern->seed = 0;
    intern->random_state = 0;

    return &intern->std;
}

/* Register Fixed context class */
void php_identifier_context_fixed_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier\\Context", "Fixed", php_identifier_context_fixed_methods);
    php_identifier_context_fixed_ce = zend_register_internal_class(&ce);
    php_identifier_context_fixed_ce->create_object = php_identifier_context_fixed_create_object;

    /* Implement Context interface */
    zend_class_implements(php_identifier_context_fixed_ce, 1, php_identifier_context_ce);

    /* Set up object handlers */
    memcpy(&php_identifier_context_fixed_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
}
