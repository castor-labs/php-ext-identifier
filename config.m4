PHP_ARG_ENABLE([identifier],
  [whether to enable identifier support],
  [AS_HELP_STRING([--enable-identifier],
    [Enable identifier support])],
  [no])

if test "$PHP_IDENTIFIER" != "no"; then
  AC_DEFINE(HAVE_IDENTIFIER, 1, [Whether you have identifier extension])
  
  PHP_NEW_EXTENSION(identifier, src/php_identifier.c src/bit128.c src/uuid.c src/uuid_version1.c src/uuid_version3.c src/uuid_version4.c src/uuid_version5.c src/uuid_version6.c src/uuid_version7.c src/ulid.c src/context.c src/context_system.c src/context_fixed.c src/codec.c, $ext_shared,, -DZEND_ENABLE_STATIC_TSRMLS_CACHE=1)
  
  PHP_SUBST(IDENTIFIER_SHARED_LIBADD)
fi
