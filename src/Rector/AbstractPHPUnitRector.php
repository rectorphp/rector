<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    protected function isPHPUnitMethodName(Node $node, string $name): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        return $this->isName($node, $name);
    }

    /**
     * @param string[] $names
     */
    protected function isPHPUnitMethodNames(Node $node, array $names): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        return $this->isNames($node, $names);
    }

    protected function isInTestClass(Node $node): bool
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        return $this->isObjectTypes($classNode, ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase']);
    }

    /**
     * @param StaticCall|MethodCall $node
     * @return StaticCall|MethodCall
     */
    protected function createPHPUnitCallWithName(Node $node, string $name): Node
    {
        return $node instanceof MethodCall ? new MethodCall($node->var, $name) : new StaticCall($node->class, $name);
    }

    private function isPHPUnitTestCaseCall(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        return $node instanceof MethodCall || $node instanceof StaticCall;
    }
}
