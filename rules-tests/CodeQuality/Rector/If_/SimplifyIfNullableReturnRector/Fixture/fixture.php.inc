<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        /** @var Property|null $property */
        $property = $this->underscoreCamelCasePropertyRenamer->rename($propertyRename);
        if (! $property instanceof Property) {
            return null;
        }

        return $property;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        return $this->underscoreCamelCasePropertyRenamer->rename($propertyRename);
    }
}

?>
