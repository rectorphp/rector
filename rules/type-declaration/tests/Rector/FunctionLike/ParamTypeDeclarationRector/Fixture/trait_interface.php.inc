<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

trait TraitWithInterfaceImplementation
{
    public function getCount(array $items)
    {
        return 5;
    }
}

interface SomeInterface
{
    /**
     * @return int
     */
    public function getCount(array $items);
}

class SomeClassWithInterfaceAndTrait implements SomeInterface
{
    use TraitWithInterfaceImplementation;
}

?>
