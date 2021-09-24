<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class RemoveOnlyPrivateProperty2
{
    public AnotherClass $dataProperty;

    private static string $unusedPropertyName;

    public function updateData() {
        $this->dataProperty::$unusedPropertyName = 'some data';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class RemoveOnlyPrivateProperty2
{
    public AnotherClass $dataProperty;

    public function updateData() {
        $this->dataProperty::$unusedPropertyName = 'some data';
    }
}

?>
