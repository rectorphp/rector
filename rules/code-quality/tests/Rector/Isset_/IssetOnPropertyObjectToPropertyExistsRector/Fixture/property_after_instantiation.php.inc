<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass2
{
    public $x;
    public $y;
}

$obj = new SomeClass2();
isset($obj->x);
isset($obj->y);
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass2
{
    public $x;
    public $y;
}

$obj = new SomeClass2();
property_exists($obj, 'x') && $obj->x !== null;
property_exists($obj, 'y') && $obj->y !== null;