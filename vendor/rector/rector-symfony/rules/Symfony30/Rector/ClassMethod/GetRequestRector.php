<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony30\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony30\Rector\ClassMethod\GetRequestRector\GetRequestRectorTest
 */
final class GetRequestRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ControllerMethodAnalyzer $controllerMethodAnalyzer;
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @var string
     */
    private const REQUEST_CLASS = 'Symfony\\Component\\HttpFoundation\\Request';
    private ?string $requestVariableAndParamName = null;
    public function __construct(ControllerMethodAnalyzer $controllerMethodAnalyzer, ControllerAnalyzer $controllerAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns fetching of Request via `$this->getRequest()` to action injection', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            $changedClassMethod = $this->refactorClassMethod($classMethod);
            if ($changedClassMethod instanceof ClassMethod) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveUniqueName(ClassMethod $classMethod, string $name) : string
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
    private function isActionWithGetRequestInBody(ClassMethod $classMethod) : bool
    {
        if (!$this->controllerMethodAnalyzer->isAction($classMethod)) {
            return \false;
        }
        $containsGetRequestMethod = $this->containsGetRequestMethod($classMethod);
        if ($containsGetRequestMethod) {
            return \true;
        }
        /** @var MethodCall[] $getMethodCalls */
        $getMethodCalls = $this->betterNodeFinder->find($classMethod, function (Node $node) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            if (!$node->var instanceof Variable) {
                return \false;
            }
            return $this->isName($node->name, 'get');
        });
        foreach ($getMethodCalls as $getMethodCall) {
            if ($this->isGetMethodCallWithRequestParameters($getMethodCall)) {
                return \true;
            }
        }
        return \false;
    }
    private function isGetRequestInAction(ClassMethod $classMethod, MethodCall $methodCall) : bool
    {
        // must be $this->getRequest() in controller
        if (!$methodCall->var instanceof Variable) {
            return \false;
        }
        if (!$this->isName($methodCall->var, 'this')) {
            return \false;
        }
        if (!$this->isName($methodCall->name, 'getRequest') && !$this->isGetMethodCallWithRequestParameters($methodCall)) {
            return \false;
        }
        return $this->controllerMethodAnalyzer->isAction($classMethod);
    }
    private function containsGetRequestMethod(ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->find((array) $classMethod->stmts, function (Node $node) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            if (!$node->var instanceof Variable) {
                return \false;
            }
            if (!$this->isName($node->var, 'this')) {
                return \false;
            }
            return $this->isName($node->name, 'getRequest');
        });
    }
    private function isGetMethodCallWithRequestParameters(MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'get')) {
            return \false;
        }
        if (\count($methodCall->args) !== 1) {
            return \false;
        }
        $firstArg = $methodCall->getArgs()[0];
        if (!$firstArg->value instanceof String_) {
            return \false;
        }
        $string = $firstArg->value;
        return $string->value === 'request';
    }
    private function getRequestVariableAndParamName() : string
    {
        if ($this->requestVariableAndParamName === null) {
            throw new ShouldNotHappenException();
        }
        return $this->requestVariableAndParamName;
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $this->requestVariableAndParamName = $this->resolveUniqueName($classMethod, 'request');
        if (!$this->isActionWithGetRequestInBody($classMethod)) {
            return null;
        }
        $fullyQualified = new FullyQualified(self::REQUEST_CLASS);
        $classMethod->params[] = new Param(new Variable($this->getRequestVariableAndParamName()), null, $fullyQualified);
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($classMethod) : ?Variable {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($this->isGetRequestInAction($classMethod, $node)) {
                return new Variable($this->getRequestVariableAndParamName());
            }
            return null;
        });
        return $classMethod;
    }
}
