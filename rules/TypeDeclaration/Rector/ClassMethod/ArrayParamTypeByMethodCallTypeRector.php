<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Handles the array param type group, including nullable arrays.
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayParamTypeByMethodCallTypeRector\ArrayParamTypeByMethodCallTypeRectorTest
 */
final class ArrayParamTypeByMethodCallTypeRector extends \Rector\TypeDeclaration\Rector\ClassMethod\AbstractParamTypeByMethodCallTypeRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change array param type based on passed method call type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(array $values)
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
    public function run(array $values)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go(array $value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
)]);
    }
    protected function isMatchingParamType(Type $type): bool
    {
        return TypeCombinator::removeNull($type)->isArray()->yes();
    }
}
