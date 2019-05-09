<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class KnownStaticConflicts
{
    public function getMoreWhatever()
    {
        if (true) {
            return false;
        }

        return [];
    }
}
