<?php

namespace Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency;

abstract class AbstractParentClass
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    public function __construct(SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

class FirstChild extends AbstractParentClass
{
    public function __construct(SomeDependency $someDependency)
    {
        parent::__construct($someDependency);
    }
}

class SecondChild extends AbstractParentClass
{
    public function __construct(SomeDependency $someDependency)
    {
        parent::__construct($someDependency);
    }
}

?>
-----
<?php

namespace Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency;

abstract class AbstractParentClass
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    public function injectAbstractParentClass(\Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

class FirstChild extends AbstractParentClass
{
}

class SecondChild extends AbstractParentClass
{
}

?>
