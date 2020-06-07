<?php

namespace Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Fixture;

use Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Source\PropertyTypeClass;

final class BooleanNotOnProperty
{
    private PropertyTypeClass $propertyTypeClass;

    public function run()
    {
        if (! $this->propertyTypeClass) {
            $this->propertyTypeClass = new PropertyTypeClass();
        }
    }
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Fixture;

use Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Source\PropertyTypeClass;

final class BooleanNotOnProperty
{
    private ?PropertyTypeClass $propertyTypeClass = null;

    public function run()
    {
        if (! $this->propertyTypeClass) {
            $this->propertyTypeClass = new PropertyTypeClass();
        }
    }
}

?>
