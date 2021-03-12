<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ParentObjectReturnInterface;

final class SkipParentOverridenWithThrows implements ParentObjectReturnInterface
{
    public function hydrate(): object
    {
        throw new ShouldNotHappenException();
    }
}
