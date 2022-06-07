<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
interface TypeWithClassTypeSpecifierInterface
{
    public function match(ObjectType $objectType, Scope $scope) : bool;
    public function resolveObjectReferenceType(ObjectType $objectType, Scope $scope) : TypeWithClassName;
}
