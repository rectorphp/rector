<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class SkipEmptyArrayOverride
{
    /**
     * @return BundleInterface[]
     */
    public function getValues()
    {
        return [];
    }
}
