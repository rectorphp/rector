<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Enum;

final class NonAssertStaticableMethods
{
    /**
     * @var string[]
     */
    public const ALL = ['createMock', 'atLeast', 'atLeastOnce', 'once', 'never', 'expectException', 'expectExceptionMessage', 'expectExceptionCode', 'expectExceptionMessageMatches'];
}
