<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\ParentClassToRemoveConstructorParamsBy;
use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\SomeDependencyToBeRemoved;

final class ClassWithExternalConstant extends ParentClassToRemoveConstructorParamsBy
{
    /**
     * @var SomeDependencyToBeRemoved
     */
    private $someDependencyToBeRemoved;

    public function __construct(SomeDependencyToBeRemoved $someDependencyToBeRemoved)
    {
        $this->someDependencyToBeRemoved = $someDependencyToBeRemoved;
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\ParentClassToRemoveConstructorParamsBy;
use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\SomeDependencyToBeRemoved;

final class ClassWithExternalConstant extends ParentClassToRemoveConstructorParamsBy
{
    /**
     * @var SomeDependencyToBeRemoved
     */
    private $someDependencyToBeRemoved;

    public function __construct()
    {
        $this->someDependencyToBeRemoved = $someDependencyToBeRemoved;
    }
}

?>
