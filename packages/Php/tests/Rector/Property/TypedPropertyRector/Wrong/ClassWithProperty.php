<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Wrong;

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
