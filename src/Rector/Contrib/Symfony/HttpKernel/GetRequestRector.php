<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\Contrib\Symfony\ControllerMethodAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 *     public action()
 *     {
 *         $this->getRequest()->...();
 *
 * into:
 *     public action(Request $request)
 *     {
 *         $request->...();
 *     }
 */
final class GetRequestRector extends AbstractRector
{
    /**
     * @var ControllerMethodAnalyzer
     */
    private $controllerMethodAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(
        ControllerMethodAnalyzer $controllerMethodAnalyzer,
        MethodCallAnalyzer $methodCallAnalyzer,
        NodeFactory $nodeFactory
    ) {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->nodeFactory = $nodeFactory;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if ($this->isActionWithGetRequestInBody($node)) {
            return true;
        }

        if ($this->isGetRequestInAction($node)) {
            return true;
        }

        return false;
    }

    /**
     * @param ClassMethod|MethodCall $classMethodOrMethodCallNode
     */
    public function refactor(Node $classMethodOrMethodCallNode): ?Node
    {
        if ($classMethodOrMethodCallNode instanceof ClassMethod) {
            $requestParam = $this->nodeFactory->createParam('request', 'Symfony\Component\HttpFoundation\Request');

            $classMethodOrMethodCallNode->params[] = $requestParam;

            return $classMethodOrMethodCallNode;
        }

        return $this->nodeFactory->createVariable('request');
    }

    private function isActionWithGetRequestInBody(Node $node): bool
    {
        if (! $this->controllerMethodAnalyzer->isAction($node)) {
            return false;
        }

        /** @var ClassMethod $node */
        if (! $this->controllerMethodAnalyzer->doesNodeContain($node, '$this->getRequest()')) {
            return false;
        }

        return true;
    }

    private function isGetRequestInAction(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethod($node, 'getRequest')) {
            return false;
        }

        $methodNode = $node->getAttribute(Attribute::METHOD_NODE);

        if (! $this->controllerMethodAnalyzer->isAction($methodNode)) {
            return false;
        }

        return true;
    }
}
