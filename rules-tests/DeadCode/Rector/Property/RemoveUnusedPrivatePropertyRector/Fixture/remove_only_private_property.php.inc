<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class RemoveOnlyPrivateProperty
{
    public \stdClass $dataProperty;

    private string $unusedPropertyName;

    public function updateData() {
        $this->dataProperty->unusedPropertyName = 'some data';
    }
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class RemoveOnlyPrivateProperty
{
    public \stdClass $dataProperty;

    public function updateData() {
        $this->dataProperty->unusedPropertyName = 'some data';
    }
}
?>
