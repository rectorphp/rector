<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    /**
     * @required
     */
    public function autowireAbstractPHPUnitRector(TestsNodeAnalyzer $testsNodeAnalyzer): void
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    protected function isTestClassMethod(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isPublic()) {
            return false;
        }

        if ($this->isName($classMethod, 'test*')) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByName('test');
    }

    protected function isPHPUnitMethodName(Node $node, string $name): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        /** @var StaticCall|MethodCall $node */
        return $this->isName($node->name, $name);
    }

    /**
     * @param string[] $names
     */
    protected function isPHPUnitMethodNames(Node $node, array $names): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        /** @var MethodCall|StaticCall $node */
        return $this->isNames($node->name, $names);
    }

    protected function isInTestClass(Node $node): bool
    {
        return $this->testsNodeAnalyzer->isInTestClass($node);
    }

    /**
     * @param StaticCall|MethodCall $node
     * @return StaticCall|MethodCall
     */
    protected function createPHPUnitCallWithName(Node $node, string $name): Node
    {
        return $node instanceof MethodCall ? new MethodCall($node->var, $name) : new StaticCall($node->class, $name);
    }

    protected function isPHPUnitTestCaseCall(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        return $node instanceof MethodCall || $node instanceof StaticCall;
    }
}
