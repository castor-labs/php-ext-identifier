dnl config.m4 for extension identifier

PHP_ARG_ENABLE([identifier],
  [whether to enable identifier support],
  [AS_HELP_STRING([--enable-identifier],
    [Enable identifier support])],
  [no])

if test "$PHP_IDENTIFIER" != "no"; then
  dnl Check for PHP version
  AC_MSG_CHECKING([for PHP version])
  tmp_version=$PHP_VERSION
  if test -z "$tmp_version"; then
    if test -z "$PHP_CONFIG"; then
      AC_MSG_ERROR([php-config not found])
    fi
    PHP_IDENTIFIER_FOUND_VERSION=`$PHP_CONFIG --version`
    PHP_IDENTIFIER_FOUND_VERNUM=`echo "${PHP_IDENTIFIER_FOUND_VERSION}" | $AWK 'BEGIN { FS = "."; } { printf "%d", ([$]1 * 100 + [$]2) * 100 + [$]3;}'`
  else
    PHP_IDENTIFIER_FOUND_VERSION=$PHP_VERSION
    PHP_IDENTIFIER_FOUND_VERNUM=`echo "${PHP_IDENTIFIER_FOUND_VERSION}" | $AWK 'BEGIN { FS = "."; } { printf "%d", ([$]1 * 100 + [$]2) * 100 + [$]3;}'`
  fi

  if test "$PHP_IDENTIFIER_FOUND_VERNUM" -lt "80100"; then
    AC_MSG_ERROR([PHP 8.1.0 or later is required])
  fi
  AC_MSG_RESULT([$PHP_IDENTIFIER_FOUND_VERSION])

  dnl Define the extension
  AC_DEFINE(HAVE_IDENTIFIER, 1, [Whether you have identifier extension])

  dnl Source files to compile
  identifier_sources="src/php_identifier.c \
    src/bit128.c \
    src/codec.c \
    src/context.c \
    src/context_fixed.c \
    src/context_system.c \
    src/ulid.c \
    src/uuid.c \
    src/uuid_version1.c \
    src/uuid_version3.c \
    src/uuid_version4.c \
    src/uuid_version5.c \
    src/uuid_version6.c \
    src/uuid_version7.c"

  dnl Register the extension
  PHP_NEW_EXTENSION(identifier, $identifier_sources, $ext_shared)

  dnl Add compiler flags
  PHP_ADD_BUILD_DIR($ext_builddir/src)

  dnl Add include path for header files
  PHP_ADD_INCLUDE($ext_srcdir)
  PHP_ADD_INCLUDE($ext_srcdir/src)
fi
