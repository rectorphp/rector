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
    private bool $a;

    private bool $b;

    private int $c;

    private int $d;

    private float $e;

    private string $f;

    private object $g;

    private iterable $h;

    private self $i;
}

?>
