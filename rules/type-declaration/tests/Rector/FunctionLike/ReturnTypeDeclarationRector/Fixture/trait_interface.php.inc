<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

trait TraitWithInterfaceImplementation
{
    public function getCount($items): int
    {
        return 5;
    }
}

interface SomeInterface
{
    /**
     * @param array $items
     */
    public function getCount($items): int;
}

class SomeClassWithInterfaceAndTrait implements SomeInterface
{
    use TraitWithInterfaceImplementation;
}

?>
