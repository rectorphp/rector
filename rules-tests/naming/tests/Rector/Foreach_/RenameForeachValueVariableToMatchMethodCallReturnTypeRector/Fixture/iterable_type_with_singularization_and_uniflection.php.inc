<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\PackagesData;

class IterableTypeWithSingularizationAndUninflectionClass
{
    public function run()
    {
        $array = [];
        foreach ($this->resolvePackagesData() as $method) {
            $array[] = $method;
        }
    }

    public function resolvePackagesData(): PackagesData
    {

    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\PackagesData;

class IterableTypeWithSingularizationAndUninflectionClass
{
    public function run()
    {
        $array = [];
        foreach ($this->resolvePackagesData() as $packageData) {
            $array[] = $packageData;
        }
    }

    public function resolvePackagesData(): PackagesData
    {

    }
}

?>
