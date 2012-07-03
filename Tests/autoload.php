<?php

if (!is_readable($file = __DIR__.'/../vendor/autoload.php')) {
    throw new RuntimeException('You must install dependencies before running any tests');
}

require $file;

spl_autoload_register(function($class) {
    if (0 === strpos($class, 'ServerGrove\\LocaleBundle\\')) {
        $path = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if (!stream_resolve_include_path($path)) {
            return false;
        }

        require_once $path;

        return true;
    }
});
