<?php declare(strict_types=1);

namespace SomeNamespace;

class SomeClassExtendingAbstractEntityInheritancePersister extends \Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister
{
    public function getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type)
    {
        return parent::getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type);
    }
}
