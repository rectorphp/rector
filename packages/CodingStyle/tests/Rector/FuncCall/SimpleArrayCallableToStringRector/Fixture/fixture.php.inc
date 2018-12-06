<?php

function simpleArrayCallableToString()
{
    $paths = array_filter($paths, function ($path): bool {
        return is_dir($path);
    });

    $paths = array_filter($paths, function ($path): bool {
        return strlen($path);
    });

    $paths = array_filter($paths, function ($path): bool {
        return !is_dir($path);
    });

    $pathLength = array_map(function ($path) {
        return strlen($path);
    }, $paths);
}

?>
-----
<?php

function simpleArrayCallableToString()
{
    $paths = array_filter($paths, 'is_dir');

    $paths = array_filter($paths, 'strlen');

    $paths = array_filter($paths, function ($path): bool {
        return !is_dir($path);
    });

    $pathLength = array_map('strlen', $paths);
}

?>
