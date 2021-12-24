<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source;

final class ExternalList
{
    public const FIRST = 'first';

    public const SECOND = 'second';

    public const VALUES = [self::FIRST, self::SECOND];
}
