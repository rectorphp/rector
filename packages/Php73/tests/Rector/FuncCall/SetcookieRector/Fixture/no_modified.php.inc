<?php
if (false) {
    setcookie('name');
    setcookie('name', 'value');
    $name = 'name';
    $value = 'value';
    $expire = 3600;
    $args = [$name, $value, $expire];
    setcookie($name);
    setcookie($name, $value);
    setcookie($name, $value, $expire);
    setcookie($name, $value, ...$args);
    setcookie(...$args);
    setrawcookie('name');
    setrawcookie('name', 'value');
}
?>
