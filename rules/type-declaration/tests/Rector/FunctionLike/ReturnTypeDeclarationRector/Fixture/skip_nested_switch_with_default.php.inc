<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SkipNestedKeepSwitchWithDefault
{
    public function someMethod($value)
    {
        switch ($value) {
            case (1):
                if (mt_rand(0, 100)) {
                    return;
                }

                return 'one';
            case (2):
                return 'two';
            default:
                return 'three';
        }
    }
}
