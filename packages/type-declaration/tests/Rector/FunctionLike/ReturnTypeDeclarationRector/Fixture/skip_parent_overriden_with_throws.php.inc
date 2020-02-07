<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

use Rector\Exception\ShouldNotHappenException;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ParentObjectReturnInterface;

final class SkipParentOverridenWithThrows implements ParentObjectReturnInterface
{
    public function hydrate(): object
    {
        throw new ShouldNotHappenException();
    }
}
