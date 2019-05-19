<?php

namespace Rector\Php\Tests\Rector\Name\ReservedObjectRector\Fixture;

function myFunction(): object
{
    return new \stdClass;
}
