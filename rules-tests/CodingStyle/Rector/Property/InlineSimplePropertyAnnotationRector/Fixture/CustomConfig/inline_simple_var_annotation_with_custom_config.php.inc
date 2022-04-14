<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\CustomConfig;

final class InlineSimpleVarAnnotationWithCustomConfig
{
    /**
     * @custom-var \DateTime[]|null
     */
    private ?array $dateTimes;
}
?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\CustomConfig;

final class InlineSimpleVarAnnotationWithCustomConfig
{
    /** @custom-var \DateTime[]|null */
    private ?array $dateTimes;
}
?>
