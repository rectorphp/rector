<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Source\ParentClassWithProperty;

final class SkipAlreadyAddedType extends ParentClassWithProperty
{
    public int $name;
}
