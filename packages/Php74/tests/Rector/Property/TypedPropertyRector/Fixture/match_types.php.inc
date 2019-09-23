<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

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
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class MatchTypes
{
    /**
     * @var bool
     */
    private bool $a;

    /**
     * @var boolean
     */
    private bool $b;

    /**
     * @var int
     */
    private int $c;

    /**
     * @var integer
     */
    private int $d;

    /**
     * @var float
     */
    private float $e;

    /**
     * @var string
     */
    private string $f;

    /**
     * @var object
     */
    private object $g;

    /**
     * @var iterable
     */
    private iterable $h;

    /**
     * @var self
     */
    private self $i;
}

?>
