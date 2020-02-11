<?php

function simplifyArraySearch()
{
    $array = [];
    if (array_search('searching', $array) !== false) {
        echo 'yes!';
    }

    if (array_search('searching', $array) === true) {
        echo 'yes!';
    }

    if (array_search('searching', $array) === false) {
        echo 'no!';
    }

    if (array_search('searching', $array) !== true) {
        echo 'no!';
    }
}

?>
-----
<?php

function simplifyArraySearch()
{
    $array = [];
    if (in_array('searching', $array, true)) {
        echo 'yes!';
    }

    if (in_array('searching', $array, true)) {
        echo 'yes!';
    }

    if (!in_array('searching', $array, true)) {
        echo 'no!';
    }

    if (!in_array('searching', $array, true)) {
        echo 'no!';
    }
}

?>
