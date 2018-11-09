<?php declare(strict_types=1);

if (array_search('searching', $array) != false) {
    echo 'yes!';
}

if (array_search('searching', $array) == true) {
    echo 'yes!';
}

if (array_search('searching', $array) == false) {
    echo 'no!';
}

if (array_search('searching', $array) != true) {
    echo 'no!';
}
