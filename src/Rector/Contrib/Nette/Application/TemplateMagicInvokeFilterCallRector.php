<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Application;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->template->someFilter(...);
 *
 * After:
 * - $this->template->getLatte()->invokeFilter('someFilter', ...)
 */
final class TemplateMagicInvokeFilterCallRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isMagicMethodCallOnType($node, 'Nette\Bridges\ApplicationLatte\Template');
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): Node
    {
        $this->changeToInvokeFilterMethodCall($methodCallNode);

        $propertyFetchNode = $this->nodeFactory->clonePropertyFetch($methodCallNode->var);

        $methodCallNode->var = $this->nodeFactory->createMethodCallWithVariable($propertyFetchNode, 'getLatte');

        return $methodCallNode;
    }

    private function changeToInvokeFilterMethodCall(MethodCall $methodCallNode): void
    {
        $filterName = $methodCallNode->name->toString();
        $filterArguments = $methodCallNode->args;

        $methodCallNode->name = new Identifier('invokeFilter');

        $methodCallNode->args[0] = $this->nodeFactory->createArg($filterName);
        $methodCallNode->args = array_merge($methodCallNode->args, $filterArguments);
    }
}
