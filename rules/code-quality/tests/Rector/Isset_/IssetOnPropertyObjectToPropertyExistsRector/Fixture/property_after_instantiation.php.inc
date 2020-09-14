<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass2
{
    public $x;
}

$obj = new SomeClass2();
isset($obj->x);
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass2
{
    public $x;
}

$obj = new SomeClass2();
property_exists($obj, 'x');