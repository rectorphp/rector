<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class DefaultTypeConstantBoolAssignedBoolType2
{
    private $property = true;

    public function run(bool $property)
    {
        $this->property = $property;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class DefaultTypeConstantBoolAssignedBoolType2
{
    private bool $property = true;

    public function run(bool $property)
    {
        $this->property = $property;
    }
}

?>
