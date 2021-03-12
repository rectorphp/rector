<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Scalar\String_;

class SkipShortenClassName
{
    /**
     * @return String_[]
     */
    public function getValues(): array
    {
        $string = new String_('Yes!');
        return [$string];
    }
}
