<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCalls;

final class SomeClass
{
    public function run(ObjectWithMagicCalls $magicObject)
    {
        return $magicObject->name;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCalls;

final class SomeClass
{
    public function run(ObjectWithMagicCalls $magicObject)
    {
        return $magicObject->getName();
    }
}

?>
