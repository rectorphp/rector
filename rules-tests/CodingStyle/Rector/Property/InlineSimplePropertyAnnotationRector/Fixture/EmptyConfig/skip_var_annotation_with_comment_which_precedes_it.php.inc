<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class SkipVarAnnotationWithCommentWhichPrecedesIt
{
    /**
     * Some description
     *
     * @var string
     */
    public const SOME_CONSTANT = 'SOME_CONSTANT';
}
?>
