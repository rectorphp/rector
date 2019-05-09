<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

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
