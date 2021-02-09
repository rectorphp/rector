<?php

declare(strict_types=1);

namespace Rector\Symfony3\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\GetRequestRectorTest
 */
final class GetRequestRector extends AbstractRector
{
    /**
     * @var string
     */
    private const REQUEST_CLASS = 'Symfony\Component\HttpFoundation\Request';

    /**
     * @var string
     */
    private $requestVariableAndParamName;

    /**
     * @var ControllerMethodAnalyzer
     */
    private $controllerMethodAnalyzer;

    public function __construct(ControllerMethodAnalyzer $controllerMethodAnalyzer)
    {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
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
    public function someAction(Request $request)
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

            if ($this->isActionWithGetRequestInBody($node)) {
                $fullyQualified = new FullyQualified(self::REQUEST_CLASS);
                $node->params[] = new Param(new Variable($this->requestVariableAndParamName), null, $fullyQualified);

                return $node;
            }
        }

        if ($this->isGetRequestInAction($node)) {
            return new Variable($this->requestVariableAndParamName);
        }

        return null;
    }

    private function resolveUniqueName(ClassMethod $classMethod, string $name): string
    {
        $candidateNames = [];
        foreach ($classMethod->params as $param) {
            $candidateNames[] = $this->getName($param);
        }

        $bareName = $name;
        $prefixes = ['main', 'default'];

        while (in_array($name, $candidateNames, true)) {
            $name = array_shift($prefixes) . ucfirst($bareName);
        }

        return $name;
    }

    private function isActionWithGetRequestInBody(ClassMethod $classMethod): bool
    {
        if (! $this->controllerMethodAnalyzer->isAction($classMethod)) {
            return false;
        }

        $containsGetRequestMethod = $this->containsGetRequestMethod($classMethod);
        if ($containsGetRequestMethod) {
            return true;
        }
        /** @var MethodCall[] $getMethodCalls */
        $getMethodCalls = $this->betterNodeFinder->find($classMethod, function (Node $node): bool {
            return $this->nodeNameResolver->isLocalMethodCallNamed($node, 'get');
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

        if (! $node->var instanceof Variable) {
            return false;
        }

        // must be $this->getRequest() in controller
        if (! $this->isVariableName($node->var, 'this')) {
            return false;
        }

        if (! $this->isName($node->name, 'getRequest') && ! $this->isGetMethodCallWithRequestParameters($node)) {
            return false;
        }

        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        return $this->controllerMethodAnalyzer->isAction($classMethod);
    }

    private function containsGetRequestMethod(ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->find((array) $classMethod->stmts, function (Node $node): bool {
            return $this->nodeNameResolver->isLocalMethodCallNamed($node, 'getRequest');
        });
    }

    private function isGetMethodCallWithRequestParameters(MethodCall $methodCall): bool
    {
        if (! $this->isName($methodCall->name, 'get')) {
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
}
