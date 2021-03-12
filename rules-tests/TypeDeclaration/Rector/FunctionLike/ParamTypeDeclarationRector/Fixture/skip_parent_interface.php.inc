<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\ParentInterface;

class ImplementingClass implements ParentInterface
{
    /**
     * @param string $value
     */
    public function __construct($value) {}
}
