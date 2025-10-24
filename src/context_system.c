#include "php.h"
#include "zend_exceptions.h"
#include "php_identifier.h"

/* Arginfo declarations */
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_context_system_getInstance, 0, 0, Php\\Identifier\\Context\\System, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_system_getTimestampMs, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_system_getGregorianEpochTime, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_context_system_getRandomBytes, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

/* Try to include the random header - compatibility across PHP versions */
#if PHP_VERSION_ID >= 80200
#include "ext/random/php_random.h"
#else
#include "ext/standard/php_random.h"
#endif

/* System context object handlers */
static zend_object_handlers php_identifier_context_system_object_handlers;

/* Singleton instance */
static zval system_context_singleton;
static bool system_context_initialized = false;

/* System context methods */

/**
 * Get the singleton system context instance
 *
 * Returns the shared system context that uses real system time and
 * cryptographically secure random number generation. This is the
 * default context used when no context is specified.
 *
 * @return System The singleton system context instance
 *
 * @example
 * $context = System::getInstance();
 * $uuid = Version4::generate($context);
 * $ulid = Ulid::generate($context);
 *
 * // Same instance every time
 * $context2 = System::getInstance();
 * var_dump($context === $context2); // bool(true)
 *
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Context_System, getInstance)
{
    if (!system_context_initialized) {
        /* Create singleton instance */
        object_init_ex(&system_context_singleton, php_identifier_context_system_ce);
        system_context_initialized = true;

        /* Make sure it doesn't get garbage collected */
        Z_ADDREF(system_context_singleton);
    }

    RETURN_ZVAL(&system_context_singleton, 1, 0);
}

static PHP_METHOD(Php_Identifier_Context_System, getTimestampMs)
{
    RETURN_LONG(php_identifier_get_timestamp_ms());
}

static PHP_METHOD(Php_Identifier_Context_System, getGregorianEpochTime)
{
    RETURN_LONG(php_identifier_get_gregorian_epoch_time());
}

static PHP_METHOD(Php_Identifier_Context_System, getRandomBytes)
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

/* System context method entries */
static const zend_function_entry php_identifier_context_system_methods[] = {
    PHP_ME(Php_Identifier_Context_System, getInstance, arginfo_context_system_getInstance, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Php_Identifier_Context_System, getTimestampMs, arginfo_context_system_getTimestampMs, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Context_System, getGregorianEpochTime, arginfo_context_system_getGregorianEpochTime, ZEND_ACC_PUBLIC)
    PHP_ME(Php_Identifier_Context_System, getRandomBytes, arginfo_context_system_getRandomBytes, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* System context object creation */
static zend_object *php_identifier_context_system_create_object(zend_class_entry *ce)
{
    php_identifier_context_system_obj *intern = zend_object_alloc(sizeof(php_identifier_context_system_obj), ce);

    zend_object_std_init(&intern->std, ce);
    object_properties_init(&intern->std, ce);

    intern->std.handlers = &php_identifier_context_system_object_handlers;

    return &intern->std;
}

/* Register System context class */
void php_identifier_context_system_register_class(void)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Php\\Identifier\\Context", "System", php_identifier_context_system_methods);
    php_identifier_context_system_ce = zend_register_internal_class(&ce);
    php_identifier_context_system_ce->create_object = php_identifier_context_system_create_object;

    /* Implement Context interface */
    zend_class_implements(php_identifier_context_system_ce, 1, php_identifier_context_ce);

    /* Set up object handlers */
    memcpy(&php_identifier_context_system_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
}
