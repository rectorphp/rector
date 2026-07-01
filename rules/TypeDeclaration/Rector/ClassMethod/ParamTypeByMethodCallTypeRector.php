<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Handles the remaining compound param type group: everything that is neither a pure object,
 * a pure scalar, nor a pure array (e.g. cross-group unions like array|string, iterable, callable).
 *
 * @see \Rector\TypeDeclaration\Rector\ClassMethod\ObjectParamTypeByMethodCallTypeRector for object types
 * @see \Rector\TypeDeclaration\Rector\ClassMethod\ScalarParamTypeByMethodCallTypeRector for scalar types
 * @see \Rector\TypeDeclaration\Rector\ClassMethod\ArrayParamTypeByMethodCallTypeRector for array types
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\ParamTypeByMethodCallTypeRectorTest
 */
final class ParamTypeByMethodCallTypeRector extends \Rector\TypeDeclaration\Rector\ClassMethod\AbstractParamTypeByMethodCallTypeRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change compound param type based on passed method call type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(iterable $values)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go($value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(iterable $values)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go(iterable $value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
)]);
    }
    protected function isMatchingParamType(Type $type): bool
    {
        $bareType = TypeCombinator::removeNull($type);
        // remaining compound types: not a pure object, scalar, nor array (iterable, callable, cross-group unions)
        return !$bareType->isObject()->yes() && !$bareType->isScalar()->yes() && !$bareType->isArray()->yes();
    }
}
