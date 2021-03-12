<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

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
