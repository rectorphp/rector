<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\ParamTypeByParentCallTypeRectorTest
 */
final class ParamTypeByParentCallTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher
     */
    private $callerParamMatcher;
    public function __construct(\Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher $callerParamMatcher)
    {
        $this->callerParamMatcher = $callerParamMatcher;
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
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $parentStaticCall = $this->findParentStaticCall($node);
        if (!$parentStaticCall instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
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
            $paramType->setAttributes([]);
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
            if (!$this->isName($staticCall->class, 'parent')) {
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
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \true;
        }
        return !$classReflection->isClass();
    }
}
