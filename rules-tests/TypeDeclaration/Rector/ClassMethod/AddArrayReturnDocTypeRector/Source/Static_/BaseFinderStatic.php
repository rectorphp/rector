<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\Static_;

class BaseFinderStatic
{
    public function find(): false|static
    {
    }
}
