<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class SkipUnconfiguredAnnotation
{
    /**
     * @unconfiguredAnnotation \DateTime[]|null A datetime!
     */
    private ?array $dateTimes;
}
?>
