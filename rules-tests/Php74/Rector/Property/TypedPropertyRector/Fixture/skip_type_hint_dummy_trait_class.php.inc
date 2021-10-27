<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

trait MaterializedPathEntity
{
    /** @var self */
    protected $parent;
    
    public function setParent(self $parent = null) : static
    {
        $this->parent = $parent;

        return $this;
    }
    
    public function getParent() : static
    {
        return $this->parent;
    }
}

final class DemoFile
{
    use MaterializedPathEntity;
    
    public function run()
    {
        return $this->getParent();
    }
}
?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

trait MaterializedPathEntity
{
    protected ?\Rector\Core\Stubs\DummyTraitClass $parent = null;
    
    public function setParent(self $parent = null) : static
    {
        $this->parent = $parent;

        return $this;
    }
    
    public function getParent() : static
    {
        return $this->parent;
    }
}

final class DemoFile
{
    use MaterializedPathEntity;
    
    public function run()
    {
        return $this->getParent();
    }
}
?>
