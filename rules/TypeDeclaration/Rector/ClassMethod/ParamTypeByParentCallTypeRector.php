<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\ParamTypeByParentCallTypeRectorTest
 */
final class ParamTypeByParentCallTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private CallerParamMatcher $callerParamMatcher;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(CallerParamMatcher $callerParamMatcher, ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->callerParamMatcher = $callerParamMatcher;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change param type based on parent param type', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($this->shouldSkip($node)) {
            return null;
        }
        $parentStaticCall = $this->findParentStaticCall($node);
        if (!$parentStaticCall instanceof StaticCall) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $param) {
            // already has type, skip
            if ($param->type !== null) {
                continue;
            }
            $parentParam = $this->callerParamMatcher->matchParentParam($parentStaticCall, $param, $scope);
            if (!$parentParam instanceof Param) {
                continue;
            }
            if (!$parentParam->type instanceof Node) {
                continue;
            }
            // mimic type
            $paramType = $parentParam->type;
            // original attributes have to removed to avoid tokens crashing from origin positions
            $this->traverseNodesWithCallable($paramType, static function (Node $node) {
                $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
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
    private function findParentStaticCall(ClassMethod $classMethod) : ?StaticCall
    {
        $classMethodName = $this->getName($classMethod);
        /** @var StaticCall[] $staticCalls */
        $staticCalls = $this->betterNodeFinder->findInstanceOf($classMethod, StaticCall::class);
        foreach ($staticCalls as $staticCall) {
            if (!$this->isName($staticCall->class, ObjectReference::PARENT)) {
                continue;
            }
            if (!$this->isName($staticCall->name, $classMethodName)) {
                continue;
            }
            return $staticCall;
        }
        return null;
    }
    private function shouldSkip(ClassMethod $classMethod) : bool
    {
        if ($classMethod->params === []) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        return !$classReflection->isClass();
    }
}
