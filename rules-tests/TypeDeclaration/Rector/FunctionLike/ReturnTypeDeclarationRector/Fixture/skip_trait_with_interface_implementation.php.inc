<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

trait SkipTraitWithInterfaceImplementation
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
    use SkipTraitWithInterfaceImplementation;
}
