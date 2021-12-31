<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\RuleErrorBuilder;
use RectorPrefix20211231\PHPUnit\Framework\TestCase;
/**
 * @implements \PHPStan\Rules\Rule<InClassMethodNode>
 */
class ShouldCallParentMethodsRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return \PHPStan\Node\InClassMethodNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $methodName = $node->getOriginalNode()->name->name;
        if (!\in_array(\strtolower($methodName), ['setup', 'teardown'], \true)) {
            return [];
        }
        if ($scope->getClassReflection() === null) {
            return [];
        }
        if (!$scope->getClassReflection()->isSubclassOf(\RectorPrefix20211231\PHPUnit\Framework\TestCase::class)) {
            return [];
        }
        $parentClass = $scope->getClassReflection()->getParentClass();
        if ($parentClass === null) {
            return [];
        }
        if (!$parentClass->hasNativeMethod($methodName)) {
            return [];
        }
        $parentMethod = $parentClass->getNativeMethod($methodName);
        if ($parentMethod->getDeclaringClass()->getName() === \RectorPrefix20211231\PHPUnit\Framework\TestCase::class) {
            return [];
        }
        $hasParentCall = $this->hasParentClassCall($node->getOriginalNode()->getStmts(), \strtolower($methodName));
        if (!$hasParentCall) {
            return [\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Missing call to parent::%s() method.', $methodName))->build()];
        }
        return [];
    }
    /**
     * @param Node\Stmt[]|null $stmts
     * @param string           $methodName
     *
     * @return bool
     */
    private function hasParentClassCall(?array $stmts, string $methodName) : bool
    {
        if ($stmts === null) {
            return \false;
        }
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\StaticCall) {
                continue;
            }
            if (!$stmt->expr->class instanceof \PhpParser\Node\Name) {
                continue;
            }
            $class = (string) $stmt->expr->class;
            if (\strtolower($class) !== 'parent') {
                continue;
            }
            if (!$stmt->expr->name instanceof \PhpParser\Node\Identifier) {
                continue;
            }
            if (\strtolower($stmt->expr->name->name) === $methodName) {
                return \true;
            }
        }
        return \false;
    }
}
