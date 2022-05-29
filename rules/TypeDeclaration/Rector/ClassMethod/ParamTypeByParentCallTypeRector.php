<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\ParamTypeByParentCallTypeRectorTest
 */
final class ParamTypeByParentCallTypeRector extends \Rector\Core\Rector\AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher
     */
    private $callerParamMatcher;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher $callerParamMatcher, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->callerParamMatcher = $callerParamMatcher;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change param type based on parent param type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeControl
{
    public function __construct(string $name)
    {
    }
}

class VideoControl extends SomeControl
{
    public function __construct($name)
    {
        parent::__construct($name);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeControl
{
    public function __construct(string $name)
    {
    }
}

class VideoControl extends SomeControl
{
    public function __construct(string $name)
    {
        parent::__construct($name);
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $parentStaticCall = $this->findParentStaticCall($node);
        if (!$parentStaticCall instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $param) {
            // already has type, skip
            if ($param->type !== null) {
                continue;
            }
            $parentParam = $this->callerParamMatcher->matchParentParam($parentStaticCall, $param, $scope);
            if (!$parentParam instanceof \PhpParser\Node\Param) {
                continue;
            }
            if ($parentParam->type === null) {
                continue;
            }
            // mimic type
            $paramType = $parentParam->type;
            // original attributes have to removed to avoid tokens crashing from origin positions
            $this->traverseNodesWithCallable($paramType, function (\PhpParser\Node $node) {
                $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
                return null;
            });
            $param->type = $paramType;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function findParentStaticCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Expr\StaticCall
    {
        $classMethodName = $this->getName($classMethod);
        /** @var StaticCall[] $staticCalls */
        $staticCalls = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Expr\StaticCall::class);
        foreach ($staticCalls as $staticCall) {
            if (!$this->isName($staticCall->class, \Rector\Core\Enum\ObjectReference::PARENT)) {
                continue;
            }
            if (!$this->isName($staticCall->name, $classMethodName)) {
                continue;
            }
            return $staticCall;
        }
        return null;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($classMethod->params === []) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \true;
        }
        return !$classReflection->isClass();
    }
}
