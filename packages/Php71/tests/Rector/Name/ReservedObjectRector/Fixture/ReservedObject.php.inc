<?php

namespace Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture;

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

namespace Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture;

class SmartObject
{

}

class ChildObject extends \Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture\SmartObject
{

}

$some = new ChildObject();
if ($some instanceof \Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture\SmartObject) {
    return;
}

is_subclass_of($some, \Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture\SmartObject::class);

is_a($some, \Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture\SmartObject::class);

function someFunction(\Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture\SmartObject $reservedObject): \Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture\SmartObject {
    return $reservedObject;
}

$someObject = new \Rector\Php71\Tests\Rector\Name\ReservedObjectRector\Fixture\SmartObject;

?>
