<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Source\ParentClassWithName;

final class SkipParentGuard extends ParentClassWithName
{
    public $name;

    public function getName(): string|null
    {
        return $this->name;
    }
}
