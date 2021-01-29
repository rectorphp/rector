<?php

namespace Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

class SomeClassExtendingAbstractEntityInheritancePersister extends \Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\Persister
{
    public function getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className)
    {
        return parent::getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type, $className);
    }
}

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

class SomeClassExtendingAbstractEntityInheritancePersister extends \Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\Persister
{
    public function getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type)
    {
        return parent::getSelectJoinColumnSQL($tableAlias, $joinColumnName, $quotedColumnName, $type);
    }
}

?>
