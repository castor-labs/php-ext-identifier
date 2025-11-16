#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_identifier.h"
#include <sys/time.h>
#include <time.h>

/* Include random headers - compatibility across PHP versions */
#if PHP_VERSION_ID >= 80200
#include "ext/random/php_random.h"
#else
#include "ext/standard/php_mt_rand.h"
#include "ext/standard/php_random.h"
#endif

/* Class entries */
zend_class_entry *php_identifier_context_ce;
zend_class_entry *php_identifier_context_system_ce;
zend_class_entry *php_identifier_context_fixed_ce;
zend_class_entry *php_identifier_bit128_ce;
zend_class_entry *php_identifier_uuid_ce;
zend_class_entry *php_identifier_uuid_version1_ce;
zend_class_entry *php_identifier_uuid_version3_ce;
zend_class_entry *php_identifier_uuid_version4_ce;
zend_class_entry *php_identifier_uuid_version5_ce;
zend_class_entry *php_identifier_uuid_version6_ce;
zend_class_entry *php_identifier_uuid_version7_ce;
zend_class_entry *php_identifier_ulid_ce;
zend_class_entry *php_identifier_codec_ce;

/* Forward declaration for globals initialization */
static void php_identifier_init_globals(zend_identifier_globals *identifier_globals);

/* {{{ PHP_MINIT_FUNCTION */
PHP_MINIT_FUNCTION(identifier)
{
    /* Initialize globals */
    ZEND_INIT_MODULE_GLOBALS(identifier, php_identifier_init_globals, NULL);

    /* Register all classes */
    php_identifier_context_register_classes();
    php_identifier_bit128_register_class();
    php_identifier_uuid_register_classes();
    php_identifier_ulid_register_class();
    php_identifier_codec_init();

    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION */
PHP_MSHUTDOWN_FUNCTION(identifier)
{
    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION */
PHP_MINFO_FUNCTION(identifier)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "identifier support", "enabled");
    php_info_print_table_row(2, "Version", PHP_IDENTIFIER_VERSION);
    php_info_print_table_end();
}
/* }}} */

/* {{{ identifier_module_entry */
zend_module_entry identifier_module_entry = {
    STANDARD_MODULE_HEADER,
    "identifier",
    NULL, /* functions */
    PHP_MINIT(identifier),
    PHP_MSHUTDOWN(identifier),
    NULL, /* PHP_RINIT */
    NULL, /* PHP_RSHUTDOWN */
    PHP_MINFO(identifier),
    PHP_IDENTIFIER_VERSION,
    STANDARD_MODULE_PROPERTIES
};
/* }}} */

/* Initialize global state */
#ifdef ZTS
int identifier_globals_id;
#else
zend_identifier_globals identifier_globals;
#endif

ZEND_DECLARE_MODULE_GLOBALS(identifier)

/* Globals initialization function */
static void php_identifier_init_globals(zend_identifier_globals *identifier_globals)
{
    memset(identifier_globals, 0, sizeof(zend_identifier_globals));
    identifier_globals->ulid_last_timestamp = 0;
    memset(identifier_globals->ulid_last_randomness, 0, 10);
    identifier_globals->ulid_randomness_initialized = 0;
}

#ifdef COMPILE_DL_IDENTIFIER
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif
ZEND_GET_MODULE(identifier)
#endif

/* Utility functions */

/* Generate cryptographically secure random bytes using PHP's random_bytes */
void php_identifier_generate_random_bytes(unsigned char *buffer, size_t length)
{
    /* Try to use PHP's secure random_bytes function */
    if (php_random_bytes(buffer, length, 1) == SUCCESS) {
        return;
    }

    /* Fallback to mt_rand if php_random_bytes fails */
    php_error_docref(NULL, E_WARNING, "Failed to generate secure random bytes, falling back to mt_rand");
    for (size_t i = 0; i < length; i++) {
        zend_long random_val = php_mt_rand_range(0, 255);
        buffer[i] = (unsigned char)random_val;
    }
}

/* Get current timestamp in milliseconds using PHP's time functions */
uint64_t php_identifier_get_timestamp_ms(void)
{
    /* Use PHP's microtime for consistency with userland code */
    struct timeval tv;
    if (gettimeofday(&tv, NULL) == 0) {
        return (uint64_t)tv.tv_sec * 1000ULL + (uint64_t)tv.tv_usec / 1000ULL;
    }

    /* Fallback to time() if gettimeofday fails */
    return (uint64_t)time(NULL) * 1000ULL;
}

/* Get current timestamp in 100-nanosecond intervals since Gregorian epoch */
uint64_t php_identifier_get_gregorian_epoch_time(void)
{
    /* Gregorian epoch: October 15, 1582 00:00:00 UTC */
    /* Unix epoch: January 1, 1970 00:00:00 UTC */
    /* Difference: 12219292800 seconds = 122192928000000000 * 100ns intervals */
    const uint64_t GREGORIAN_TO_UNIX_100NS = 122192928000000000ULL;
    
    struct timeval tv;
    gettimeofday(&tv, NULL);
    
    uint64_t unix_100ns = (uint64_t)tv.tv_sec * 10000000ULL + (uint64_t)tv.tv_usec * 10ULL;
    return unix_100ns + GREGORIAN_TO_UNIX_100NS;
}
