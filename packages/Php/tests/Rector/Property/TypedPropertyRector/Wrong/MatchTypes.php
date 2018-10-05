<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Wrong;

final class MatchTypes
{
    /**
     * @var bool
     */
    private $a;

    /**
     * @var boolean
     */
    private $b;

    /**
     * @var int
     */
    private $c;

    /**
     * @var integer
     */
    private $d;

    /**
     * @var float
     */
    private $e;

    /**
     * @var string
     */
    private $f;

    /**
     * @var object
     */
    private $g;

    /**
     * @var iterable
     */
    private $h;

    /**
     * @var self
     */
    private $i;

    /**
     * @var parent
     */
    private $j;
}
