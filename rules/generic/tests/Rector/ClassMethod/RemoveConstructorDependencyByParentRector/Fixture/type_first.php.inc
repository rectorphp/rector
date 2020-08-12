<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\ParentClassToRemoveConstructorParamsBy;
use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\SomeDependencyToBeRemoved;

final class TypeFirst extends ParentClassToRemoveConstructorParamsBy
{
    /**
     * @var SomeDependencyToBeRemoved
     */
    private $whatever;

    public function __construct(SomeDependencyToBeRemoved $whatever)
    {
        $this->whatever = $whatever;
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\ParentClassToRemoveConstructorParamsBy;
use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\SomeDependencyToBeRemoved;

final class TypeFirst extends ParentClassToRemoveConstructorParamsBy
{
    /**
     * @var SomeDependencyToBeRemoved
     */
    private $whatever;

    public function __construct()
    {
        $this->whatever = $whatever;
    }
}

?>
