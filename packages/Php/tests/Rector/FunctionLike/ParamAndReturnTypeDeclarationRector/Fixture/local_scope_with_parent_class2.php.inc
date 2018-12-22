<?php // https://github.com/rectorphp/rector/issues/812

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Fixture;

final class CreditNoteRefGenerator extends AbstractAccountingRefGenerator
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
