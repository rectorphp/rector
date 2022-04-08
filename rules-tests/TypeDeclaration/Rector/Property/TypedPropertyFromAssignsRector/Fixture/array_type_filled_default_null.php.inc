<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class ArrayTypeFilledDefaultNull
{
    /**
     * @var array
     */
    private $property;

    public function __construct(array $property = null)
    {
        $this->property = $property;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class ArrayTypeFilledDefaultNull
{
    private ?array $property = null;

    public function __construct(array $property = null)
    {
        $this->property = $property;
    }
}

?>
