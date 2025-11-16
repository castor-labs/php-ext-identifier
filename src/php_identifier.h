#ifndef PHP_IDENTIFIER_H
#define PHP_IDENTIFIER_H

extern zend_module_entry identifier_module_entry;
#define phpext_identifier_ptr &identifier_module_entry

#define PHP_IDENTIFIER_VERSION "0.1.0"

#ifdef PHP_WIN32
#	define PHP_IDENTIFIER_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#	define PHP_IDENTIFIER_API __attribute__ ((visibility("default")))
#else
#	define PHP_IDENTIFIER_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

/* Thread-safe globals for ULID monotonic state */
ZEND_BEGIN_MODULE_GLOBALS(identifier)
    uint64_t ulid_last_timestamp;
    unsigned char ulid_last_randomness[10]; /* ULID_RANDOMNESS_BYTES */
    int ulid_randomness_initialized;
ZEND_END_MODULE_GLOBALS(identifier)

#ifdef ZTS
#define IDENTIFIER_G(v) TSRMG(identifier_globals_id, zend_identifier_globals *, v)
extern int identifier_globals_id;
#else
#define IDENTIFIER_G(v) (identifier_globals.v)
extern zend_identifier_globals identifier_globals;
#endif

/* Class entries */
extern zend_class_entry *php_identifier_context_ce;
extern zend_class_entry *php_identifier_context_system_ce;
extern zend_class_entry *php_identifier_context_fixed_ce;
extern zend_class_entry *php_identifier_bit128_ce;
extern zend_class_entry *php_identifier_uuid_ce;
extern zend_class_entry *php_identifier_uuid_version1_ce;
extern zend_class_entry *php_identifier_uuid_version3_ce;
extern zend_class_entry *php_identifier_uuid_version4_ce;
extern zend_class_entry *php_identifier_uuid_version5_ce;
extern zend_class_entry *php_identifier_uuid_version6_ce;
extern zend_class_entry *php_identifier_uuid_version7_ce;
extern zend_class_entry *php_identifier_ulid_ce;
extern zend_class_entry *php_identifier_codec_ce;

/* Object structures */
typedef struct _php_identifier_bit128_obj {
    unsigned char data[16];
    zend_object std;
} php_identifier_bit128_obj;

typedef struct _php_identifier_context_system_obj {
    zend_object std;
} php_identifier_context_system_obj;

typedef struct _php_identifier_context_fixed_obj {
    uint64_t timestamp_ms;
    uint32_t seed;
    uint32_t random_state;
    zend_object std;
} php_identifier_context_fixed_obj;

/* Helper macros */
#define PHP_IDENTIFIER_BIT128_OBJ_P(zv) \
    ((php_identifier_bit128_obj*)((char*)(Z_OBJ_P(zv)) - XtOffsetOf(php_identifier_bit128_obj, std)))

#define PHP_IDENTIFIER_CONTEXT_SYSTEM_OBJ_P(zv) \
    ((php_identifier_context_system_obj*)((char*)(Z_OBJ_P(zv)) - XtOffsetOf(php_identifier_context_system_obj, std)))

#define PHP_IDENTIFIER_CONTEXT_FIXED_OBJ_P(zv) \
    ((php_identifier_context_fixed_obj*)((char*)(Z_OBJ_P(zv)) - XtOffsetOf(php_identifier_context_fixed_obj, std)))

/* Function declarations */
PHP_MINIT_FUNCTION(identifier);
PHP_MSHUTDOWN_FUNCTION(identifier);
PHP_MINFO_FUNCTION(identifier);

/* Context functions */
void php_identifier_context_register_classes(void);

/* Bit128 functions */
void php_identifier_bit128_register_class(void);

/* UUID functions */
void php_identifier_uuid_register_classes(void);

/* ULID functions */
void php_identifier_ulid_register_class(void);

/* Utility functions */
void php_identifier_generate_random_bytes(unsigned char *buffer, size_t length);
uint64_t php_identifier_get_timestamp_ms(void);
uint64_t php_identifier_get_gregorian_epoch_time(void);

/* Codec initialization */
void php_identifier_codec_init(void);

#endif /* PHP_IDENTIFIER_H */
