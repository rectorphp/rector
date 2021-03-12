<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

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
