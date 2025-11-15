#include "php.h"
#include "php_identifier.h"

/* Context interface methods */
static const zend_function_entry php_identifier_context_methods[] = {
    PHP_FE_END
};

/* Forward declarations */
void php_identifier_context_system_register_class(void);
void php_identifier_context_fixed_register_class(void);

/* Register Context interface and implementations */
void php_identifier_context_register_classes(void)
{
    zend_class_entry ce;

    /* Register Context interface */
    INIT_NS_CLASS_ENTRY(ce, "Identifier", "Context", php_identifier_context_methods);
    php_identifier_context_ce = zend_register_internal_interface(&ce);

    /* Interface methods are defined by implementing classes */
    /* No need to register method signatures for interfaces in PHP extensions */
    /* The interface contract is enforced at runtime */

    /* Register implementations */
    php_identifier_context_system_register_class();
    php_identifier_context_fixed_register_class();
}
