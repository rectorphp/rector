<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Wrong;

final class DefaultValues
{
    /**
     * @var bool
     */
    private $name = 'not_a_bool';

    private bool $isItRealName = false;

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

    private ?array $nullableItems = null;

    private float $a = 42.42;

    private float $b = 42;

    /**
     * @var float
     */
    private $c = 'hey';

    /**
     * @var int
     */
    private $e = 42.42;

    private int $f = 42;

    private array $g = [1, 2, 3];

    private iterable $h = [1, 2, 3];
}
