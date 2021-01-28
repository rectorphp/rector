<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
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

    /**
     * @param string[] $names
     */
    protected function isPHPUnitMethodNames(Node $node, array $names): bool
    {
        if (! $this->testsNodeAnalyzer->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        /** @var MethodCall|StaticCall $node */
        return $this->isNames($node->name, $names);
    }

    /**
     * @param StaticCall|MethodCall $node
     * @return StaticCall|MethodCall
     */
    protected function createPHPUnitCallWithName(Node $node, string $name): Node
    {
        return $node instanceof MethodCall ? new MethodCall($node->var, $name) : new StaticCall($node->class, $name);
    }
}
