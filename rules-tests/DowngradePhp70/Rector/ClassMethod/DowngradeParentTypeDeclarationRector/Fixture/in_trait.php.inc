<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

trait SomeTrait
{
    public function run(): parent
    {
        return $this;
    }
}

class ParentClass
{

}

class SomeClass extends ParentClass
{
    use SomeTrait;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

trait SomeTrait
{
    /**
     * @return parent
     */
    public function run()
    {
        return $this;
    }
}

class ParentClass
{

}

class SomeClass extends ParentClass
{
    use SomeTrait;
}

?>
