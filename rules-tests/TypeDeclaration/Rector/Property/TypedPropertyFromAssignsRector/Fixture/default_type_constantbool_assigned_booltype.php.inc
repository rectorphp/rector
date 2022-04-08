<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class DefaultTypeConstantBoolAssignedBoolType
{
    private $property = true;

    public function run()
    {
        $this->property = false;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class DefaultTypeConstantBoolAssignedBoolType
{
    private bool $property = true;

    public function run()
    {
        $this->property = false;
    }
}

?>
