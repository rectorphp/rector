<?php

function run()
{
    $_a = 'a';
    $_some_value = $_a;
    (function ($_some_value) use ($_a) {
        $_some_value;
        $_a;
    });
}
-----
<?php

function run()
{
    $a = 'a';
    $someValue = $a;
    (function ($someValue) use ($a) {
        $someValue;
        $a;
    });
}
