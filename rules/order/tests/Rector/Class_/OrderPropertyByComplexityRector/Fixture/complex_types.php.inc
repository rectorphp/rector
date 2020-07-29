<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Fixture;

use Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Source\AnotherSimpleType;
use Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Source\ComplexType;

class ComplexTypes
{
    /**
     * Static cache
     *
     * @var string[][]
     */
    private static $arrayOfStringsNested = [];

    /**
     * @var int
     */
    private $integer;

    /**
     * @var ComplexType
     */
    private $anotherObject;

    /**
     * @var string[]
     */
    private $arrayOfStrings = [];

    /**
     * @var AnotherSimpleType
     */
    private $someObject;

    /**
     * @var callable
     */
    private $callableProperty;

    /**
     * @var mixed
     */
    private $mixedProperty;

    /**
     * @var mixed[]
     *
     * Rich information about methods, e.g.:
     *
     *  0 => array (6)
     *     |  start => 18
     *     |  visibility => "public" (6)
     *     |  static => FALSE
     *     |  type => "method" (6)
     *     |  name => "secondMethod" (12)
     *     |  end => 29
     */
    private $arrayOfMixedPlusContent = [];
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Fixture;

use Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Source\AnotherSimpleType;
use Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\Source\ComplexType;

class ComplexTypes
{
    /**
     * @var int
     */
    private $integer;
    /**
     * Static cache
     *
     * @var string[][]
     */
    private static $arrayOfStringsNested = [];
    /**
     * @var string[]
     */
    private $arrayOfStrings = [];
    /**
     * @var mixed[]
     *
     * Rich information about methods, e.g.:
     *
     *  0 => array (6)
     *     |  start => 18
     *     |  visibility => "public" (6)
     *     |  static => FALSE
     *     |  type => "method" (6)
     *     |  name => "secondMethod" (12)
     *     |  end => 29
     */
    private $arrayOfMixedPlusContent = [];
    /**
     * @var ComplexType
     */
    private $anotherObject;
    /**
     * @var AnotherSimpleType
     */
    private $someObject;
    /**
     * @var mixed
     */
    private $mixedProperty;
    /**
     * @var callable
     */
    private $callableProperty;
}

?>
