<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Handles the scalar (string/int/float/bool) param type group.
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ScalarParamTypeByMethodCallTypeRector\ScalarParamTypeByMethodCallTypeRectorTest
 */
final class ScalarParamTypeByMethodCallTypeRector extends \Rector\TypeDeclaration\Rector\ClassMethod\AbstractParamTypeByMethodCallTypeRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change scalar param type based on passed method call type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(int $value)
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
    public function run(int $value)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go(int $value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
)]);
    }
    protected function isMatchingParamType(Type $type): bool
    {
        $type = TypeCombinator::removeNull($type);
        if (!$type->isScalar()->yes()) {
            return \false;
        }
        // a string param accepts int/float/bool via scalar coercion, so a caller
        // may pass another scalar - inferring string from it would be unsafe
        return !$type->isString()->yes();
    }
}
