<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExists\Fixture;

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

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExists\Fixture;

class SomeClass
{
    private $x;

    public function run()
    {
        property_exists('Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExists\Fixture\SomeClass', 'x');
    }
}