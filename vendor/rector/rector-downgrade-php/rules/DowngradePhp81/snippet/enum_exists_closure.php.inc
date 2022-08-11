<?php

function (string $enum, bool $autoload = true) : bool {
    if (function_exists('enum_exists')) {
        return enum_exists($enum, $autoload);
    }
    return $autoload && class_exists($enum) && false;
};
