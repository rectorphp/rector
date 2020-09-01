<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Scalar;

final class SkipAlreadyClassString
{
    /**
     * @return class-string[]
     */
    public function getFluentCalls(): array
    {
        return [Scalar::class];
    }
}
