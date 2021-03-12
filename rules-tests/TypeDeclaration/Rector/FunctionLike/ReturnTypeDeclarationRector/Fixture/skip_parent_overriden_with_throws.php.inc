<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Core\Exception\ShouldNotHappenException;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ParentObjectReturnInterface;

final class SkipParentOverridenWithThrows implements ParentObjectReturnInterface
{
    public function hydrate(): object
    {
        throw new ShouldNotHappenException();
    }
}
