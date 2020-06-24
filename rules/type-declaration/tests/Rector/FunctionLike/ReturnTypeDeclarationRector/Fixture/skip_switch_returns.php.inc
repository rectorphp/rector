<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class SkipSwtichReturns
{
    public function someFunction($value)
    {
        switch ($value) {
            case (1):
                return 'one';
            case (2):
                return 'two';
        }
    }
}
