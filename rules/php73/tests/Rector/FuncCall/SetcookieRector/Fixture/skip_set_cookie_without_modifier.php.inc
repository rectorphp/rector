<?php

namespace Rector\Php73\Tests\Rector\FuncCall\SetcookieRector\Fixture;

class SkipSetCookieWithoutModifier
{
    public function run()
    {
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
}
