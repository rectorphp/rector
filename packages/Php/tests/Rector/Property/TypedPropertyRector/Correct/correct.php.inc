<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Wrong;

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
