<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class InlineSimpleVarAnnotationAboveClassConstant
{
    /**
     * @var string[]
     */
    private const AXES = ['x', 'y', 'z'];
}
?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class InlineSimpleVarAnnotationAboveClassConstant
{
    /** @var string[] */
    private const AXES = ['x', 'y', 'z'];
}
?>
