<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Core\Exception\ShouldNotHappenException;

class SkipExceptionVoid
{
    /**
     * @return int
     */
    public function run($value)
    {
        if ($value === 5) {
            return 100;
        }

        throw new ShouldNotHappenException();
    }
}
