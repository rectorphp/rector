<?php declare(strict_types=1);

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
