<?php

namespace Rector\CodeQuality\Tests\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector\Fixture;

function simplifyEmptyCheck()
{
    $invalid = is_array($var) && in_array('foo', $var);
    $almostValid = is_array($var) && count($var) > 0;
    $invalid2 = isset($this->events[$event]) && !empty($this->events[$event]);
    $completelyInvalid = !$value instanceof \Foo && !$value instanceof \Bar;

    if (empty($this->orders) && empty($this->unionOrders)) {
        throw new \RuntimeException('You must specify an orderBy clause when using this function.');
    }

    echo is_array($objects) && is_array($objects);
}
