<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

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
