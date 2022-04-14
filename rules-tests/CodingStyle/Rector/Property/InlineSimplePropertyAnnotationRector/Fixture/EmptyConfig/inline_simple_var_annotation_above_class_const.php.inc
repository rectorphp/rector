<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class InlineSimpleVarAnnotationAboveClassConstant
{
    /**
     * @var string[]
     */
    private const AXES = ['x', 'y', 'z'];

    /**
     * @phpstan-var int[]
     */
    public const NUMBERS = [1, 2, 3];

    /**
     * @psalm-var \DateTime[]|null
     */
    private ?array $dateTimes;
}
?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class InlineSimpleVarAnnotationAboveClassConstant
{
    /** @var string[] */
    private const AXES = ['x', 'y', 'z'];

    /** @phpstan-var int[] */
    public const NUMBERS = [1, 2, 3];

    /** @psalm-var \DateTime[]|null */
    private ?array $dateTimes;
}
?>
