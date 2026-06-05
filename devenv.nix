{ pkgs, lib, ... }:

let
  php = pkgs.php83;
  zig = pkgs.zig_0_15 or pkgs.zig;
  zls = pkgs.zls_0_15 or pkgs.zls;
in
{
  packages = [
    php
    (lib.lowPrio php.unwrapped.dev) # provides php-config and PHP development headers
    pkgs.php83Packages.composer
    zig
    zls
  ];

  scripts.build.exec = "zig build";
  scripts.test.exec = ''
    zig build
    composer install
    composer test
  '';
  scripts.dev.exec = "zig build dev";
  scripts.bench.exec = ''
    zig build
    composer install
    composer bench
  '';

  enterShell = ''
    echo "PHP $(php -r 'echo PHP_VERSION;')"
    echo "Zig $(zig version)"
    echo "ZLS $(zls --version)"
    echo "php-config $(php-config --version)"
    composer --version --no-ansi 2>/dev/null | head -n1
    echo
    echo "Available scripts: build, test, dev, bench"
  '';

  enterTest = ''
    php -v
    php-config --version
    composer --version
    zig version
    zls --version
    zig build test
  '';
}
