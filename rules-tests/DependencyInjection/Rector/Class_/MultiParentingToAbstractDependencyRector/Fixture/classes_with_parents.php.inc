<?php

namespace Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\ClassWithParent;

abstract class AbstractParentClassWithParents
{
    /**
     * @var ClassWithParent
     */
    private $implement;

    public function __construct(ClassWithParent $implement)
    {
        $this->implement = $implement;
    }
}

class FirstChildClassWithParent extends AbstractParentClassWithParents
{
    public function __construct(ClassWithParent $firstImplementer)
    {
        parent::__construct($firstImplementer);
    }
}

class SecondChildClassWithParent extends AbstractParentClassWithParents
{
    public function __construct(ClassWithParent $secondImplementer)
    {
        parent::__construct($secondImplementer);
    }
}

?>
-----
<?php

namespace Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\ClassWithParent;

abstract class AbstractParentClassWithParents
{
    /**
     * @var ClassWithParent
     */
    private $implement;

    public function injectAbstractParentClassWithParents(\Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\ClassWithParent $classWithParent)
    {
        $this->classWithParent = $classWithParent;
    }
}

class FirstChildClassWithParent extends AbstractParentClassWithParents
{
}

class SecondChildClassWithParent extends AbstractParentClassWithParents
{
}

?>
