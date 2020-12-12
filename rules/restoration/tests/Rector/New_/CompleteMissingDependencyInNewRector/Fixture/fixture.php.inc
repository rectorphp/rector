<?php

namespace Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Fixture;

use Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomValueObject;

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

namespace Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Fixture;

use Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomValueObject;

final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject(new \Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomDependency());
    }
}

?>
