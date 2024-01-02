<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\DeadCode\NodeCollector\UnusedParameterResolver;
use Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
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
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(VariadicFunctionLikeDetector $variadicFunctionLikeDetector, UnusedParameterResolver $unusedParameterResolver, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->variadicFunctionLikeDetector = $variadicFunctionLikeDetector;
        $this->unusedParameterResolver = $unusedParameterResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            $unusedParameters = $this->unusedParameterResolver->resolve($classMethod);
            if ($unusedParameters === []) {
                continue;
            }
            $unusedParameterPositions = \array_keys($unusedParameters);
            foreach (\array_keys($classMethod->params) as $key) {
                if (!\in_array($key, $unusedParameterPositions, \true)) {
                    continue;
                }
                unset($classMethod->params[$key]);
            }
            // reset param keys
            $classMethod->params = \array_values($classMethod->params);
            $this->clearPhpDocInfo($classMethod, $unusedParameters);
            $this->removeCallerArgs($node, $classMethod, $unusedParameters);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param Param[] $unusedParameters
     */
    private function removeCallerArgs(Class_ $class, ClassMethod $classMethod, array $unusedParameters) : void
    {
        $classMethods = $class->getMethods();
        if ($classMethods === []) {
            return;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $keysArg = \array_keys($unusedParameters);
        foreach ($classMethods as $classMethod) {
            /** @var MethodCall[] $callers */
            $callers = $this->resolveCallers($classMethod, $methodName);
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
        if ($methodCall->isFirstClassCallable()) {
            return;
        }
        $args = $methodCall->getArgs();
        foreach (\array_keys($args) as $key) {
            if (\in_array($key, $keysArg, \true)) {
                unset($args[$key]);
            }
        }
        // reset arg keys
        $methodCall->args = \array_values($args);
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
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
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
        $hasChanged = \false;
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
            $hasTagRemoved = $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
            if ($hasTagRemoved) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        }
    }
}
