<?php

// https://github.com/rectorphp/rector/issues/812

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

final class SkipParentAbstractClassWithoutType extends AbstractAccountingRefGenerator
{
    /**
     * @param CreditNote $entity
     */
    public function getReferenceDate($entity): \DateTime
    {
    }
}

abstract class AbstractAccountingRefGenerator
{
    public function getReferenceDate($entity): \DateTime
    {
    }
}
