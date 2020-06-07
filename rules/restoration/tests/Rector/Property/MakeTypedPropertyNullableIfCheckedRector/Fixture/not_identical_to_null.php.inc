<?php

namespace Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Fixture;

use Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Source\PropertyTypeClass;

final class NotIdenticalToNull
{
    private PropertyTypeClass $propertyTypeClass;

    public function run()
    {
        if ($this->propertyTypeClass !== null) {
            return $this->propertyTypeClass;
        }
    }
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Fixture;

use Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\Source\PropertyTypeClass;

final class NotIdenticalToNull
{
    private ?PropertyTypeClass $propertyTypeClass = null;

    public function run()
    {
        if ($this->propertyTypeClass !== null) {
            return $this->propertyTypeClass;
        }
    }
}

?>
