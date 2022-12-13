<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPUnit\Framework\TestCase;
use function in_array;
use function sprintf;
use function strtolower;
/**
 * @implements Rule<InClassMethodNode>
 */
class ShouldCallParentMethodsRule implements Rule
{
    public function getNodeType() : string
    {
        return InClassMethodNode::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $methodName = $node->getOriginalNode()->name->name;
        if (!in_array(strtolower($methodName), ['setup', 'teardown'], \true)) {
            return [];
        }
        if ($scope->getClassReflection() === null) {
            return [];
        }
        if (!$scope->getClassReflection()->isSubclassOf(TestCase::class)) {
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
        if ($parentMethod->getDeclaringClass()->getName() === TestCase::class) {
            return [];
        }
        $hasParentCall = $this->hasParentClassCall($node->getOriginalNode()->getStmts(), strtolower($methodName));
        if (!$hasParentCall) {
            return [RuleErrorBuilder::message(sprintf('Missing call to parent::%s() method.', $methodName))->build()];
        }
        return [];
    }
    /**
     * @param Node\Stmt[]|null $stmts
     *
     */
    private function hasParentClassCall(?array $stmts, string $methodName) : bool
    {
        if ($stmts === null) {
            return \false;
        }
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Node\Expr\StaticCall) {
                continue;
            }
            if (!$stmt->expr->class instanceof Node\Name) {
                continue;
            }
            $class = (string) $stmt->expr->class;
            if (strtolower($class) !== 'parent') {
                continue;
            }
            if (!$stmt->expr->name instanceof Node\Identifier) {
                continue;
            }
            if ($stmt->expr->name->toLowerString() === $methodName) {
                return \true;
            }
        }
        return \false;
    }
}
