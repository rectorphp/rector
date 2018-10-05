<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Wrong;

final class DefaultValues
{
    /**
     * @var bool
     */
    private $name = 'not_a_bool';

    /**
     * @var bool
     */
    private $isItRealName = false;

    /**
     * @var bool
     */
    private $isItRealNameNull = null;

    /**
     * @var string
     */
    private $size = false;

    /**
     * @var array
     */
    private $items = null;

    /**
     * @var iterable
     */
    private $itemsB = null;

    /**
     * @var array|null
     */
    private $nullableItems = null;

    /**
     * @var float
     */
    private $a = 42.42;

    /**
     * @var float
     */
    private $b = 42;

    /**
     * @var float
     */
    private $c = 'hey';

    /**
     * @var int
     */
    private $e = 42.42;

    /**
     * @var int
     */
    private $f = 42;

    /**
     * @var array
     */
    private $g = [1, 2, 3];

    /**
     * @var iterable
     */
    private $h = [1, 2, 3];
}
