<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\GetRequestRector\GetRequestRectorTest
 */
final class GetRequestRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const REQUEST_CLASS = 'Symfony\\Component\\HttpFoundation\\Request';
    /**
     * @var string|null
     */
    private $requestVariableAndParamName;
    /**
     * @var \Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer
     */
    private $controllerMethodAnalyzer;
    public function __construct(\Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer $controllerMethodAnalyzer)
    {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeController
{
    public function someAction()
    {
        $this->getRequest()->...();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;

class SomeController
{
    public function someAction(Request $request)
    {
        $request->...();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $this->requestVariableAndParamName = $this->resolveUniqueName($node, 'request');
            if ($this->isActionWithGetRequestInBody($node)) {
                $fullyQualified = new \PhpParser\Node\Name\FullyQualified(self::REQUEST_CLASS);
                $node->params[] = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($this->getRequestVariableAndParamName()), null, $fullyQualified);
                return $node;
            }
        }
        if ($this->isGetRequestInAction($node)) {
            return new \PhpParser\Node\Expr\Variable($this->getRequestVariableAndParamName());
        }
        return null;
    }
    private function resolveUniqueName(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $name) : string
    {
        $candidateNames = [];
        foreach ($classMethod->params as $param) {
            $candidateNames[] = $this->getName($param);
        }
        $bareName = $name;
        $prefixes = ['main', 'default'];
        while (\in_array($name, $candidateNames, \true)) {
            $name = \array_shift($prefixes) . \ucfirst($bareName);
        }
        return $name;
    }
    private function isActionWithGetRequestInBody(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$this->controllerMethodAnalyzer->isAction($classMethod)) {
            return \false;
        }
        $containsGetRequestMethod = $this->containsGetRequestMethod($classMethod);
        if ($containsGetRequestMethod) {
            return \true;
        }
        /** @var MethodCall[] $getMethodCalls */
        $getMethodCalls = $this->betterNodeFinder->find($classMethod, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->name, 'get');
        });
        foreach ($getMethodCalls as $getMethodCall) {
            if ($this->isGetMethodCallWithRequestParameters($getMethodCall)) {
                return \true;
            }
        }
        return \false;
    }
    private function isGetRequestInAction(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        // must be $this->getRequest() in controller
        if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->var, 'this')) {
            return \false;
        }
        if (!$this->isName($node->name, 'getRequest') && !$this->isGetMethodCallWithRequestParameters($node)) {
            return \false;
        }
        $classMethod = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        return $this->controllerMethodAnalyzer->isAction($classMethod);
    }
    private function containsGetRequestMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->find((array) $classMethod->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            if (!$this->isName($node->var, 'this')) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->name, 'getRequest');
        });
    }
    private function isGetMethodCallWithRequestParameters(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'get')) {
            return \false;
        }
        if (\count($methodCall->args) !== 1) {
            return \false;
        }
        if (!$methodCall->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        /** @var String_ $stringValue */
        $stringValue = $methodCall->args[0]->value;
        return $stringValue->value === 'request';
    }
    private function getRequestVariableAndParamName() : string
    {
        if ($this->requestVariableAndParamName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->requestVariableAndParamName;
    }
}
