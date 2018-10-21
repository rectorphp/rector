<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\HttpKernel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Rector\Utils\BetterNodeFinder;

final class GetRequestRector extends AbstractRector
{
    /**
     * @var string
     */
    private const REQUEST_CLASS = 'Symfony\Component\HttpFoundation\Request';

    /**
     * @var ControllerMethodAnalyzer
     */
    private $controllerMethodAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var string
     */
    private $requestVariableAndParamName;

    public function __construct(
        ControllerMethodAnalyzer $controllerMethodAnalyzer,
        NodeFactory $nodeFactory,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->nodeFactory = $nodeFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeController
{
    public function someAction()
    {
        $this->getRequest()->...();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;

class SomeController
{
    public action(Request $request)
    {
        $request->...();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            $this->requestVariableAndParamName = $this->resolveUniqueName($node, 'request');
        }

        if ($this->isGetRequestInAction($node)) {
            return $this->nodeFactory->createVariable($this->requestVariableAndParamName);
        }

        if ($this->isActionWithGetRequestInBody($node)) {
            $requestParam = $this->nodeFactory->createParam($this->requestVariableAndParamName, self::REQUEST_CLASS);

            $node->params[] = $requestParam;

            return $node;
        }

        return null;
    }

    private function isActionWithGetRequestInBody(Node $node): bool
    {
        if (! $this->controllerMethodAnalyzer->isAction($node)) {
            return false;
        }

        // "$this->getRequest()"
        $isGetRequestMethod = (bool) $this->betterNodeFinder->find($node, function (Node $node) {
            return $this->isName($node, 'getRequest');
        });

        if ($isGetRequestMethod) {
            return true;
        }

        // "$this->get('request')"
        /** @var MethodCall[] $getMethodCalls */
        $getMethodCalls = $this->betterNodeFinder->find($node, function (Node $node) {
            return $this->isName($node, 'get');
        });

        foreach ($getMethodCalls as $getMethodCall) {
            if ($this->isGetMethodCallWithRequestParameters($getMethodCall)) {
                return true;
            }
        }

        return false;
    }

    private function isGetRequestInAction(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $this->isName($node, 'getRequest') && ! $this->isGetMethodCallWithRequestParameters($node)) {
            return false;
        }

        $methodNode = $node->getAttribute(Attribute::METHOD_NODE);

        return $this->controllerMethodAnalyzer->isAction($methodNode);
    }

    private function isGetMethodCallWithRequestParameters(MethodCall $methodCall): bool
    {
        if (! $this->isName($methodCall, 'get')) {
            return false;
        }

        if (count($methodCall->args) !== 1) {
            return false;
        }

        if (! $methodCall->args[0]->value instanceof String_) {
            return false;
        }

        /** @var String_ $stringValue */
        $stringValue = $methodCall->args[0]->value;

        return $stringValue->value === 'request';
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    private function resolveUniqueName(Node $node, string $name): string
    {
        if ($node instanceof ClassMethod) {
            $candidates = $node->params;
        } else {
            $candidates = $node->args;
        }

        $candidateNames = [];
        foreach ($candidates as $candidate) {
            $candidateNames[] = $this->getName($candidate);
        }

        $bareName = $name;
        $prefixes = ['main', 'default'];

        while (in_array($name, $candidateNames, true)) {
            $name = array_shift($prefixes) . ucfirst($bareName);
        }

        return $name;
    }
}
