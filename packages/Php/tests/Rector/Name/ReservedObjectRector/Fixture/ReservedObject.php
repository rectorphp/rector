<?php

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

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong;

class SmartObject
{

}

class ChildObject extends \Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong\SmartObject
{

}

$some = new ChildObject();
if ($some instanceof \Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong\SmartObject) {
    return;
}

is_subclass_of($some, \Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong\SmartObject::class);

is_a($some, \Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong\SmartObject::class);

function someFunction(\Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong\SmartObject $reservedObject): \Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong\SmartObject {
    return $reservedObject;
}

$someObject = new \Rector\Php\Tests\Rector\Name\ReservedObjectRector\Wrong\SmartObject;

?>
