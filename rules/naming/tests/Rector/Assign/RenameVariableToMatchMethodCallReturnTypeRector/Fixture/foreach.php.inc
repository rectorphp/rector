<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameVariableToMatchMethodCallReturnTypeRector\Source\Method;

class ForeachClass
{
    public function run()
    {
        $array = [];
        foreach ($this->getMethods() as $property) {
            $array[] = $property;
        }
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {

    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameVariableToMatchMethodCallReturnTypeRector\Source\Method;

class ForeachClass
{
    public function run()
    {
        $array = [];
        foreach ($this->getMethods() as $method) {
            $array[] = $method;
        }
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {

    }
}

?>
