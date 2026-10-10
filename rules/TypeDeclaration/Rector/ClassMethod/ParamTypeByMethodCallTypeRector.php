<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as it fills invalid types and can cause type errors on controllers and other input calls. Inferring param types from a single caller is unreliable and needs human verification, so this better suits static analysis.
 */
final class ParamTypeByMethodCallTypeRector extends \Rector\TypeDeclaration\Rector\ClassMethod\AbstractParamTypeByMethodCallTypeRector implements DeprecatedInterface
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
}
