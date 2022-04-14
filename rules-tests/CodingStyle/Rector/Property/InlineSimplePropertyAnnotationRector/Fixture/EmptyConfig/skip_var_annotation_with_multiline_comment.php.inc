<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class InlineVarAnnotationWithMultilineComment
{
    /**
     * @var \DateTime[]|null A datetime with multiline
     * description!
     */
    private ?array $dateTimes;
}
?>
