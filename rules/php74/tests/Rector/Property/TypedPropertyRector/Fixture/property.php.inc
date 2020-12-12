<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class ClassWithProperty
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var int|null|bool
     */
    private $multiCount;

    /**
     * @var void
     */
    private $shouldBeSkipped;

    /**
     * @var callable
     */
    private $shouldBeSkippedToo;

    /**
     * @var callable|null
     */
    private $shouldBeSkippedAlso;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class ClassWithProperty
{
    private int $count;

    /**
     * @var int|null|bool
     */
    private $multiCount;

    /**
     * @var void
     */
    private $shouldBeSkipped;

    /**
     * @var callable
     */
    private $shouldBeSkippedToo;

    /**
     * @var callable|null
     */
    private $shouldBeSkippedAlso;
}

?>
