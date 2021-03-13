<?php

namespace Rector\Tests\Restoration\Rector\New_\CompleteMissingDependencyInNewRector\Fixture;

use Rector\Tests\Restoration\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomValueObject;

final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject();
    }
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\New_\CompleteMissingDependencyInNewRector\Fixture;

use Rector\Tests\Restoration\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomValueObject;

final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject(new \Rector\Tests\Restoration\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomDependency());
    }
}

?>
