<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCalls;

final class AssignToSetter
{
    public function run(ObjectWithMagicCalls $magicObject)
    {
        $magicObject->name = 'Matthias';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCalls;

final class AssignToSetter
{
    public function run(ObjectWithMagicCalls $magicObject)
    {
        $magicObject->setName('Matthias');
    }
}

?>
