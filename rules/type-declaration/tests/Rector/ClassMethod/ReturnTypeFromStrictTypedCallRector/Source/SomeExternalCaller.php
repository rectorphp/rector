<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source;

final class SomeExternalCaller
{
    public function getName(): string
    {
        return 'Yesman';
    }
}
