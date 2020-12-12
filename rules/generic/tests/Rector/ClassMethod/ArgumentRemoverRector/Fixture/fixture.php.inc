<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

class SomeClassExtendingAbstractEntityInheritancePersister extends \Rector\Generic\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\Persister
{
    public function getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className)
    {
        return parent::getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className);
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

class SomeClassExtendingAbstractEntityInheritancePersister extends \Rector\Generic\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\Persister
{
    public function getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type)
    {
        return parent::getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type);
    }
}

?>
