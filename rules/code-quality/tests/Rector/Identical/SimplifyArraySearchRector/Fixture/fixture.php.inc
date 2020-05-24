<?php

function simplifyArraySearch($mixed, array $array, bool $bool)
{
    return [
        array_search('searching', $array) !== false,
        array_search('searching', $array, true) !== false,
        // ---
        array_search($mixed, $array) !== false,
        false !== array_search($mixed, $array),
        array_search($mixed, $array) === false,
        false === array_search($mixed, $array),
        array_search($mixed, $array, $bool) !== false,
        false !== array_search($mixed, $array, $bool),
        array_search($mixed, $array, $bool) === false,
        false === array_search($mixed, $array, $bool),
    ];
}

?>
-----
<?php

function simplifyArraySearch($mixed, array $array, bool $bool)
{
    return [
        in_array('searching', $array),
        in_array('searching', $array, true),
        // ---
        in_array($mixed, $array),
        in_array($mixed, $array),
        !in_array($mixed, $array),
        !in_array($mixed, $array),
        in_array($mixed, $array, $bool),
        in_array($mixed, $array, $bool),
        !in_array($mixed, $array, $bool),
        !in_array($mixed, $array, $bool),
    ];
}

?>
