<?php

// https://github.com/rectorphp/rector/issues/812

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\AbstractClassWithoutParamType;
use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\PremiumDependency;

final class SkipParentAbstractClassWithoutType extends AbstractClassWithoutParamType
{
    /**
     * @param PremiumDependency $entity
     */
    public function getReferenceDate($entity): \DateTime
    {
    }
}
