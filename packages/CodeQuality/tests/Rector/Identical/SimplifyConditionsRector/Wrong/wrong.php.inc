<?php declare(strict_types=1);

if (! ($foo !== 'bar')) {
} elseif (! ($foo != 'bug')) {
}

function foo ($bar) {
    return ! ($bar > 0);
}

$baz = ! ($foo === 'baz');

function bar ($foo) {
    return ! ($foo >= 0);
}
