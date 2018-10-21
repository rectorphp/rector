<?php declare(strict_types=1);

namespace SomeNamespace;

class SomeClassExtendingAbstractEntityInheritancePersister extends \Rector\Tests\Rector\Argument\ArgumentRemoverRector\Source\Persister
{
    public function getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className)
    {
        return parent::getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className);
    }
}
