<?php declare(strict_types=1);

$someObject = new stdClass();
if ('stdClass' === get_class($someObject)) {
    echo 'yes!';
}

if (get_class($someObject) === stdClass::class) {
    echo 'yes!';
}

if ('stdClass' !== get_class($someObject)) {
    echo 'no!';
}

if (get_class($someObject) !== stdClass::class) {
    echo 'no!';
}
