<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\RenameParamToMatchTypeRectorTest
 */
final class RenameParamToMatchTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\Naming\Guard\BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ExpectedNameResolver
     */
    private $expectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver
     */
    private $matchParamTypeExpectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\ValueObjectFactory\ParamRenameFactory
     */
    private $paramRenameFactory;
    /**
     * @readonly
     * @var \Rector\Naming\ParamRenamer\ParamRenamer
     */
    private $paramRenamer;
    public function __construct(\Rector\Naming\Guard\BreakingVariableRenameGuard $breakingVariableRenameGuard, \Rector\Naming\Naming\ExpectedNameResolver $expectedNameResolver, \Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver, \Rector\Naming\ValueObjectFactory\ParamRenameFactory $paramRenameFactory, \Rector\Naming\ParamRenamer\ParamRenamer $paramRenamer)
    {
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
        $this->paramRenameFactory = $paramRenameFactory;
        $this->paramRenamer = $paramRenamer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Rename param to match ClassType', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $pie)
    {
        $food = $pie;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $apple)
    {
        $food = $apple;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->hasChanged = \false;
        foreach ($node->params as $param) {
            $expectedName = $this->expectedNameResolver->resolveForParamIfNotYet($param);
            if ($expectedName === null) {
                continue;
            }
            if ($this->shouldSkipParam($param, $expectedName, $node)) {
                continue;
            }
            $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($expectedName === null) {
                continue;
            }
            $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($param, $expectedName);
            if (!$paramRename instanceof \Rector\Naming\ValueObject\ParamRename) {
                continue;
            }
            $this->paramRenamer->rename($paramRename);
            $this->hasChanged = \true;
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $classMethod
     */
    private function shouldSkipParam(\PhpParser\Node\Param $param, string $expectedName, $classMethod) : bool
    {
        /** @var string $paramName */
        $paramName = $this->getName($param);
        if ($this->breakingVariableRenameGuard->shouldSkipParam($paramName, $expectedName, $classMethod, $param)) {
            return \true;
        }
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        // promoted property
        if (!$this->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return \false;
        }
        return $param->flags !== 0;
    }
}
