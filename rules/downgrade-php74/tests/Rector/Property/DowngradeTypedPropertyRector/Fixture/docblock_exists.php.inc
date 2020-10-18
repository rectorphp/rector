<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class DocBlockExists {
    /**
     * This property is the best one
     */
    private ?string $property;

    /**
     * This property is also fine
     * @var string[]
     */
    private array $arrayProperty;

    /**
     * This property is the worst
     * @var int
     */
    private float $wronglyAnnotatedProperty;

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

class DocBlockExists {
    /**
     * This property is the best one
     * @var string|null
     */
    private $property;

    /**
     * This property is also fine
     * @var string[]
     */
    private $arrayProperty;

    /**
     * This property is the worst
     * @var int
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
