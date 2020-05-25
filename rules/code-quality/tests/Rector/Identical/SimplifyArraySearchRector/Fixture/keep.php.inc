<?php

function simplifyArraySearch2(array $array)
{
    return [
        array_search('searching', $array) != false,
        array_search('searching', $array) == false,
        array_search('searching', $array) !== true,
        array_search('searching', $array) === true,
        array_search('searching', $array) !== 0,
        array_search('searching', $array) === 0,
    ];
}

?>
