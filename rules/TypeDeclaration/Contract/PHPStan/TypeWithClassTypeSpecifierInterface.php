<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\Contract\PHPStan;

use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
interface TypeWithClassTypeSpecifierInterface
{
    public function match(ObjectType $objectType, Scope $scope) : bool;
    public function resolveObjectReferenceType(ObjectType $objectType, Scope $scope) : TypeWithClassName;
}
