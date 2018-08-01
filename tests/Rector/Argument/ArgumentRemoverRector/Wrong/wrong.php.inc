<?php declare(strict_types=1);

namespace SomeNamespace;

class SomeClassExtendingAbstractEntityInheritancePersister extends \Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister
{
    public function getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className)
    {
        return parent::getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className);
    }
}
