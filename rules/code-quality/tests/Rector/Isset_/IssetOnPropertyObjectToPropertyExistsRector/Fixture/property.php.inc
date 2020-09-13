<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass
{
    private $x;

    public function run()
    {
        isset($this->x);
    }
}
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass
{
    private $x;

    public function run()
    {
        property_exists('Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture\SomeClass', 'x');
    }
}