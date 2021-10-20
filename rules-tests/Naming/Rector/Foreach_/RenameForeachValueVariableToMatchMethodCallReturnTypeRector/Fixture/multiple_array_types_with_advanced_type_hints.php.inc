<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\Method;

class SomeClass
{
    public function simpleArray()
    {
        $array = [];
        foreach ($this->getVariants() as $property) {
            $array[] = $property;
        }
    }

    public function associativeArray()
    {
        $associativeArray = [];
        foreach ($this->getNamesAndVariants() as $name => $value) {
            $associativeArray[$name] = $value;
        }
    }

    /**
     * @return Method[]
     */
    public function getVariants(): array
    {

    }

    /**
     * @return array<string, Method>
     */
    public function getNamesAndVariants(): array
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\Method;

class SomeClass
{
    public function simpleArray()
    {
        $array = [];
        foreach ($this->getVariants() as $method) {
            $array[] = $method;
        }
    }

    public function associativeArray()
    {
        $associativeArray = [];
        foreach ($this->getNamesAndVariants() as $name => $method) {
            $associativeArray[$name] = $method;
        }
    }

    /**
     * @return Method[]
     */
    public function getVariants(): array
    {

    }

    /**
     * @return array<string, Method>
     */
    public function getNamesAndVariants(): array
    {

    }
}

?>
