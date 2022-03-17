<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Source;

class ParentClassWithArgs
{
    public function redirect(string $path, $args = [])
    {
    }
}
