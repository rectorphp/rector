<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\NodeDependencies;

class IterableTypeWithSingularizationClass
{
    public function run()
    {
        $array = [];
        foreach ($this->resolveDependencies() as $method) {
            $array[] = $method;
        }
    }

    public function resolveDependencies(): NodeDependencies
    {

    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\NodeDependencies;

class IterableTypeWithSingularizationClass
{
    public function run()
    {
        $array = [];
        foreach ($this->resolveDependencies() as $nodeDependency) {
            $array[] = $nodeDependency;
        }
    }

    public function resolveDependencies(): NodeDependencies
    {

    }
}

?>
