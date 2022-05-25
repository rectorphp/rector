<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source;

abstract class ParentClassWithDefinedReturnSecond
{
    /**
     * @return mixed[]
     */
    public function getData()
    {
        return ['...'];
    }
}
