<?php

namespace Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture;

function myFunction(): object
{
    return new \stdClass;
}
