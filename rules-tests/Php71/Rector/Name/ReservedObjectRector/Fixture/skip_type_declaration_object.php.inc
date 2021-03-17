<?php

namespace Rector\Tests\Php71\Rector\Name\ReservedObjectRector\Fixture;

function myFunction(): object
{
    return new \stdClass;
}
