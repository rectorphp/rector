<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Builder\Method\MethodStatementCollector;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Covers ref. https://github.com/RectorPHP/Rector/issues/79
 */
final class ExceptionRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodStatementCollector
     */
    private $methodStatementCollector;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodStatementCollector $methodStatementCollector,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodStatementCollector = $methodStatementCollector;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        return $this->methodCallAnalyzer->isMethodCallMethod(
            $node,
            'setExpectedException'
        );
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $methodCallNode->name->name = 'expectException';

        // 2nd argument move to standalone method...

        if (isset($methodCallNode->args[1])) {
            $secondArgument = $methodCallNode->args[1];
            unset($methodCallNode->args[1]);

            /** @var Node $parentNode */
            $parentNode = $methodCallNode->getAttribute(Attribute::PARENT_NODE);
            $parentParentNode = $parentNode->getAttribute(Attribute::PARENT_NODE);

            $expectExceptionMessageMethodCall = $this->nodeFactory->createMethodCallWithArguments(
                'this',
                'expectExceptionMessage',
                [$secondArgument]
            );

            $expressionNode = new Expression($expectExceptionMessageMethodCall);

            $this->methodStatementCollector->addStatementForMethod(
                $parentParentNode,
                $expressionNode
            );
        }

        return $methodCallNode;
    }

    private function isInTestClass(Node $node): bool
    {
        $className = $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith($className, 'Test');
    }
}
