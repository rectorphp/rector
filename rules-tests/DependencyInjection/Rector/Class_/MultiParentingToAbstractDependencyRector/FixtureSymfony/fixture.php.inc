<?php

namespace Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\FixtureSymfony;

use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency;

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

namespace Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\FixtureSymfony;

use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency;

abstract class AbstractParentClass
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    /**
     * @required
     */
    public function injectAbstractParentClass(\Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency $someDependency)
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
