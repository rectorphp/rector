<?php

namespace Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Fixture;

use Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Source\PropertyTypeClass;

final class SomeClass
{
    private PropertyTypeClass $propertyTypeClass;

    public function run()
    {
        if ($this->propertyTypeClass === null) {
            $this->propertyTypeClass = new PropertyTypeClass;
        }

        if (null === $this->propertyTypeClass) {
            $this->propertyTypeClass = new PropertyTypeClass;
        }
    }
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Fixture;

use Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Source\PropertyTypeClass;

final class SomeClass
{
    private ?PropertyTypeClass $propertyTypeClass = null;

    public function run()
    {
        if ($this->propertyTypeClass === null) {
            $this->propertyTypeClass = new PropertyTypeClass;
        }

        if (null === $this->propertyTypeClass) {
            $this->propertyTypeClass = new PropertyTypeClass;
        }
    }
}

?>
