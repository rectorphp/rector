<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class NotInTestsTypedPropertyInTests
{
    /**
     * @var int
     */
    private $value;

    public function setUp(): void
    {
        $this->value = 1000;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class NotInTestsTypedPropertyInTests
{
    private ?int $value = null;

    public function setUp(): void
    {
        $this->value = 1000;
    }
}

?>
