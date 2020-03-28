<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Rector\Core\Exception\NotImplementedException;
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

            $ruleError = $this->createRuleError($node, $scope, $methodName, $methodVisibility);
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

    private function createRuleError(Node $node, Scope $scope, string $methodName, string $methodVisibility): RuleError
    {
        $message = sprintf(
            'Change "%s()" method visibility to "%s" to respect parent method visibility.',
            $methodName,
            $methodVisibility
        );

        $ruleErrorBuilder = RuleErrorBuilder::message($message);
        $ruleErrorBuilder->line($node->getLine());
        $ruleErrorBuilder->file($scope->getFile());

        return $ruleErrorBuilder->build();
    }
}
