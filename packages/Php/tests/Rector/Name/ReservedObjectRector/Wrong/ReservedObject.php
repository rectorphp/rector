<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong;

class ReservedObject
{

}

class ChildObject extends ReservedObject
{

}

$some = new ChildObject();
if ($some instanceof ReservedObject) {
    return;
}

is_subclass_of($some, ReservedObject::class);

is_a($some, ReservedObject::class);

function someFunction(ReservedObject $reservedObject): ReservedObject {
    return $reservedObject;
}

$someObject = new ReservedObject;
