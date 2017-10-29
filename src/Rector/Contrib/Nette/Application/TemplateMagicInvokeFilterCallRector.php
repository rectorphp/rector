<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Application;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\MethodCallNodeFactory;
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

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        NodeFactory $nodeFactory,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isTypeAndMagic($node, 'Nette\Bridges\ApplicationLatte\Template');
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): Node
    {
        $this->changeToInvokeFilterMethodCall($methodCallNode);

        $methodCallNode->var = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $methodCallNode->var,
            'getLatte'
        );

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
