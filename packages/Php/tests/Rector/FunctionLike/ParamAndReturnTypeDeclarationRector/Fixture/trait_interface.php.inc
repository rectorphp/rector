<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

trait TraitWithInterfaceImplementation
{
    public function getCount($items)
    {
        return 5;
    }
}

interface SomeInterface
{
    /**
     * @param array $items
     * @return int
     */
    public function getCount($items);
}

class SomeClassWithInterfaceAndTrait implements SomeInterface
{
    use TraitWithInterfaceImplementation;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

trait TraitWithInterfaceImplementation
{
    public function getCount(array $items): int
    {
        return 5;
    }
}

interface SomeInterface
{
    /**
     * @param array $items
     * @return int
     */
    public function getCount(array $items): int;
}

class SomeClassWithInterfaceAndTrait implements SomeInterface
{
    use TraitWithInterfaceImplementation;
}

?>
