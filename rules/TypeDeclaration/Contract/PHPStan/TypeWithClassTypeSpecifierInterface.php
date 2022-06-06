<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
interface TypeWithClassTypeSpecifierInterface
{
    public function match(\PHPStan\Type\ObjectType $objectType, \PHPStan\Analyser\Scope $scope) : bool;
    public function resolveObjectReferenceType(\PHPStan\Type\ObjectType $objectType, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\TypeWithClassName;
}
