<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class NullableClassString
{
    /**
     * @var class-string
     */
    private $property;

    public function fill()
    {
        $this->property = 'stdClass';
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class NullableClassString
{
    /**
     * @var class-string
     */
    private ?string $property = null;

    public function fill()
    {
        $this->property = 'stdClass';
    }
}

?>
