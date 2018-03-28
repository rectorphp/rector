<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;

/**
 * - Before:
 * - $this->assertTrue($foo instanceof Foo, 'message');
 * - $this->assertFalse($foo instanceof Foo, 'message');
 *
 * - After:
 * - $this->assertInstanceOf('Foo', $foo, 'message');
 * - $this->assertNotInstanceOf('Foo', $foo, 'message');
 */
final class AssertInstanceOfComparisonRector extends AbstractPHPUnitRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods($node, ['assertTrue', 'assertFalse'])) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;

        return $firstArgumentValue instanceof Instanceof_;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->renameMethod($methodCallNode);
        $this->changeOrderArguments($methodCallNode);

        return $methodCallNode;
    }

    public function changeOrderArguments(MethodCall $methodCallNode): void
    {
        $oldArguments = $methodCallNode->args;
        /** @var Instanceof_ $comparison */
        $comparison = $oldArguments[0]->value;

        $className = $comparison->class->toCodeString();
        $argument = $comparison->expr;

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge([
            $this->nodeFactory->createRelativeClassConstantReference($className),
            $argument,
        ], $oldArguments);
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        $this->identifierRenamer->renameNodeWithMap($methodCallNode, [
            'assertTrue' => 'assertInstanceOf',
            'assertFalse' => 'assertNotInstanceOf',
        ]);
    }
}
