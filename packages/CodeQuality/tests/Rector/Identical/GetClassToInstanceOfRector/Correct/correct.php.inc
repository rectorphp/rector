<?php declare(strict_types=1);

$someObject = new stdClass();
if ($someObject instanceof \stdClass) {
    echo 'yes!';
}

if ($someObject instanceof \stdClass) {
    echo 'yes!';
}

if (!$someObject instanceof \stdClass) {
    echo 'no!';
}

if (!$someObject instanceof \stdClass) {
    echo 'no!';
}
