<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Source\ParentInterface;

class ImplementingClass implements ParentInterface
{
    /**
     * @param string $value
     */
    public function __construct($value) {}
}
