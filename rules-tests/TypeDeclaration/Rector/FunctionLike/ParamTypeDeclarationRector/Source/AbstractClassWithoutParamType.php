<?php
declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source;

abstract class AbstractClassWithoutParamType
{
    public function getReferenceDate($entity): \DateTime
    {
    }
}
