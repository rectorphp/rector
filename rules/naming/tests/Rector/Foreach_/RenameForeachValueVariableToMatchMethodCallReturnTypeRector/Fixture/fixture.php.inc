<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\Method;

class SomeClass
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

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\Method;

class SomeClass
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
