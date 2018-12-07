<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

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
     * @var bool
     * another comment
     */
    private $isTrue = false;

    /**
     * @var void
     */
    private $shouldBeSkipped;

    /**
     * @var callable
     */
    private $shouldBeSkippedToo;

    /**
     * @var invalid
     */
    private $cantTouchThis;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class ClassWithProperty
{
    private int $count;

    /**
     * @var int|null|bool
     */
    private $multiCount;

    /**
     * another comment
     */
    private bool $isTrue = false;

    /**
     * @var void
     */
    private $shouldBeSkipped;

    /**
     * @var callable
     */
    private $shouldBeSkippedToo;

    /**
     * @var invalid
     */
    private $cantTouchThis;
}

?>
