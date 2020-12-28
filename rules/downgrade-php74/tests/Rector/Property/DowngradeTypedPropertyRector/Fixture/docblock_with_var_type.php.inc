<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class DocblockWithVarType {
    /**
     * This property is amazing
     * @var string[]
     */
    private array $arrayProperty;

    /**
     * This property is more than amazing
     * @var array<string, string>
     */
    private array $associativeArrayProperty;

    /**
     * This property is misleading
     * @var int
     */
    private float $misleadinglyAnnotatedProperty;

    /**
     * This property is the worst
     * @var string
     */
    private bool $wronglyAnnotatedProperty;

    /**
     * This property allows less than the annotation suggests
     * @var int|null
     */
    private int $widerAnnotatedProperty;

    /**
     * This property allows more than the annotation suggests
     * @var int
     */
    private ?int $stricterAnnotatedProperty;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class DocblockWithVarType {
    /**
     * This property is amazing
     * @var string[]
     */
    private $arrayProperty;

    /**
     * This property is more than amazing
     * @var array<string, string>
     */
    private $associativeArrayProperty;

    /**
     * This property is misleading
     * @var int
     */
    private $misleadinglyAnnotatedProperty;

    /**
     * This property is the worst
     * @var string
     */
    private $wronglyAnnotatedProperty;

    /**
     * This property allows less than the annotation suggests
     * @var int|null
     */
    private $widerAnnotatedProperty;

    /**
     * This property allows more than the annotation suggests
     * @var int
     */
    private $stricterAnnotatedProperty;
}

?>
