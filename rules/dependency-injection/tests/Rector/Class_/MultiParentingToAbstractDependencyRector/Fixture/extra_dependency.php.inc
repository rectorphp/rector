<?php

namespace Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\AnotherDependency;
use Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency;

abstract class AbstractParentClassWithExtraDependency
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

class FirstChildWithExtraDependency extends AbstractParentClassWithExtraDependency
{
    /**
     * @var AnotherDependency
     */
    private $anotherDependency;

    public function __construct(SomeDependency $someDependency, AnotherDependency $anotherDependency)
    {
        parent::__construct($someDependency);
        $this->anotherDependency = $anotherDependency;
    }
}

class SecondChildWithExtraDependency extends AbstractParentClassWithExtraDependency
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

use Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\AnotherDependency;
use Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency;

abstract class AbstractParentClassWithExtraDependency
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    public function injectAbstractParentClassWithExtraDependency(\Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

class FirstChildWithExtraDependency extends AbstractParentClassWithExtraDependency
{
    /**
     * @var AnotherDependency
     */
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }
}

class SecondChildWithExtraDependency extends AbstractParentClassWithExtraDependency
{
}

?>
