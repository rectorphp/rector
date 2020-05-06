<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Rector\Core\Exception\NotImplementedException;
use ReflectionMethod;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\PreventParentMethodVisibilityOverrideRule\PreventParentMethodVisibilityOverrideRuleTest
 */
final class PreventParentMethodVisibilityOverrideRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Change "%s()" method visibility to "%s" to respect parent method visibility.';

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->getClassReflection() === null) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection->getParentClassesNames() === []) {
            return [];
        }

        $methodName = (string) $node->name;

        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            if (! method_exists($parentClassName, $methodName)) {
                continue;
            }

            $parentReflectionMethod = new ReflectionMethod($parentClassName, $methodName);
            if ($this->isClassMethodCompatibleWithParentReflectionMethod($node, $parentReflectionMethod)) {
                return [];
            }

            $methodVisibility = $this->resolveReflectionMethodVisibilityAsStrings($parentReflectionMethod);

            $errorMessage = sprintf(self::ERROR_MESSAGE, $methodName, $methodVisibility);
            return [$errorMessage];
        }

        return [];
    }

    private function isClassMethodCompatibleWithParentReflectionMethod(
        ClassMethod $classMethod,
        ReflectionMethod $reflectionMethod
    ): bool {
        if ($reflectionMethod->isPublic() && $classMethod->isPublic()) {
            return true;
        }

        if ($reflectionMethod->isProtected() && $classMethod->isProtected()) {
            return true;
        }

        return $reflectionMethod->isPrivate() && $classMethod->isPrivate();
    }

    private function resolveReflectionMethodVisibilityAsStrings(ReflectionMethod $reflectionMethod): string
    {
        if ($reflectionMethod->isPublic()) {
            return 'public';
        }

        if ($reflectionMethod->isProtected()) {
            return 'protected';
        }

        if ($reflectionMethod->isPrivate()) {
            return 'private';
        }

        throw new NotImplementedException();
    }
}
