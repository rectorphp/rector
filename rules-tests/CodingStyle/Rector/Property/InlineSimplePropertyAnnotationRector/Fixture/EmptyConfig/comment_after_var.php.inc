<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class SkipCommentAfterVar
{
    /**
     * @phpstan-var string[]
     *
     * some comment
     */
    private const POSSIBLE_DELIMITERS = ['#', '~', '/'];
}