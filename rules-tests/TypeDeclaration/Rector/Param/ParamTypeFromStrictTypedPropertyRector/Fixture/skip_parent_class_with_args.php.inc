<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Source\ParentClassWithArgs;

final class SkipParentClassWithArgs extends ParentClassWithArgs
{
    private array $items = [];

    public function redirect(string $path, $args = [])
    {
        $this->items = $args;
    }
}
