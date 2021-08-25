<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

interface SomeInterface
{
    public function run(): parent;
}

class ParentClass
{

}

class SomeClass extends ParentClass implements SomeInterface
{
    public function run(): parent
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\Fixture;

interface SomeInterface
{
    /**
     * @return parent
     */
    public function run();
}

class ParentClass
{

}

class SomeClass extends ParentClass implements SomeInterface
{
    /**
     * @return parent
     */
    public function run()
    {
        return $this;
    }
}

?>
