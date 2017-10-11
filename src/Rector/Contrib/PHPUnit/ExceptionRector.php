<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
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
        return $this->methodCallAnalyzer->isMethodCallMethod(
            $node,
            'setExpectedException'
        );
    }

    /**
     * @param MethodCall $methodCallNode
     * @return null|Node
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

            $this->methodStatementCollector->addStatementForMethod($parentParentNode,
                $this->nodeFactory->createMethodCallWithArguments('this', 'expectExceptionMessage', [$secondArgument])
            );
        }

        return $methodCallNode;
    }
}
