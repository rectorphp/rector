<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeCollector\UnusedParameterResolver;
use Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\RemoveUnusedPrivateMethodParameterRectorTest
 */
final class RemoveUnusedPrivateMethodParameterRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector
     */
    private $variadicFunctionLikeDetector;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeCollector\UnusedParameterResolver
     */
    private $unusedParameterResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(VariadicFunctionLikeDetector $variadicFunctionLikeDetector, UnusedParameterResolver $unusedParameterResolver, PhpDocTagRemover $phpDocTagRemover)
    {
        $this->variadicFunctionLikeDetector = $variadicFunctionLikeDetector;
        $this->unusedParameterResolver = $unusedParameterResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter, if not required by interface or parent class', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private function run($value, $value2)
    {
         $this->value = $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private function run($value)
    {
         $this->value = $value;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $unusedParameters = $this->unusedParameterResolver->resolve($node);
        if ($unusedParameters === []) {
            return null;
        }
        $this->nodeRemover->removeNodes($unusedParameters);
        $this->clearPhpDocInfo($node, $unusedParameters);
        $this->removeCallerArgs($node, $unusedParameters);
        return $node;
    }
    /**
     * @param Param[] $unusedParameters
     */
    private function removeCallerArgs(ClassMethod $classMethod, array $unusedParameters) : void
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return;
        }
        $methods = $classLike->getMethods();
        if ($methods === []) {
            return;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $keysArg = \array_keys($unusedParameters);
        foreach ($methods as $method) {
            /** @var MethodCall[] $callers */
            $callers = $this->resolveCallers($method, $methodName);
            if ($callers === []) {
                continue;
            }
            foreach ($callers as $caller) {
                $this->cleanupArgs($caller, $keysArg);
            }
        }
    }
    /**
     * @param int[] $keysArg
     */
    private function cleanupArgs(MethodCall $methodCall, array $keysArg) : void
    {
        $args = $methodCall->getArgs();
        foreach (\array_keys($args) as $key) {
            if (\in_array($key, $keysArg, \true)) {
                unset($args[$key]);
            }
        }
        $methodCall->args = $args;
    }
    /**
     * @return MethodCall[]
     */
    private function resolveCallers(ClassMethod $classMethod, string $methodName) : array
    {
        return $this->betterNodeFinder->find($classMethod, function (Node $subNode) use($methodName) : bool {
            if (!$subNode instanceof MethodCall) {
                return \false;
            }
            if ($subNode->isFirstClassCallable()) {
                return \false;
            }
            if (!$subNode->var instanceof Variable) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode->name, $methodName);
        });
    }
    private function shouldSkip(ClassMethod $classMethod) : bool
    {
        if (!$classMethod->isPrivate()) {
            return \true;
        }
        if ($classMethod->params === []) {
            return \true;
        }
        return $this->variadicFunctionLikeDetector->isVariadic($classMethod);
    }
    /**
     * @param Param[] $unusedParameters
     */
    private function clearPhpDocInfo(ClassMethod $classMethod, array $unusedParameters) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        foreach ($unusedParameters as $unusedParameter) {
            $parameterName = $this->getName($unusedParameter->var);
            if ($parameterName === null) {
                continue;
            }
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterName);
            if (!$paramTagValueNode instanceof ParamTagValueNode) {
                continue;
            }
            if ($paramTagValueNode->parameterName !== '$' . $parameterName) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
        }
    }
}
