<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrors\RuleErrorWithMessageAndLineAndFile;
use Rector\Exception\NotImplementedException;
use ReflectionMethod;

final class PreventParentMethodVisibilityOverrideRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     * @return RuleError[]
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

            $ruleError = new RuleErrorWithMessageAndLineAndFile(
                sprintf(
                    'Change "%s()" method visibility to "%s" to respect parent method visibility.',
                    $methodName,
                    $methodVisibility
                ),
                $node->getLine(),
                $scope->getFile()
            );

            return [$ruleError];
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

        if ($reflectionMethod->isPrivate() && $classMethod->isPrivate()) {
            return true;
        }

        return false;
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
