<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\VariableRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\RenameVariableToMatchNewTypeRectorTest
 */
final class RenameVariableToMatchNewTypeRector extends \Rector\Core\Rector\AbstractRector
{
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
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
    public function __construct(\Rector\Naming\Guard\BreakingVariableRenameGuard $breakingVariableRenameGuard, \Rector\Naming\Naming\ExpectedNameResolver $expectedNameResolver, \Rector\Naming\VariableRenamer $variableRenamer)
    {
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->variableRenamer = $variableRenamer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Rename variable to match new ClassType', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $search = new DreamSearch();
        $search->advance();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $dreamSearch = new DreamSearch();
        $dreamSearch->advance();
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
        $hasChanged = \false;
        $assignsOfNew = $this->getAssignsOfNew($node);
        foreach ($assignsOfNew as $assignOfNew) {
            $expectedName = $this->expectedNameResolver->resolveForAssignNew($assignOfNew);
            /** @var Variable $variable */
            $variable = $assignOfNew->var;
            if ($expectedName === null) {
                continue;
            }
            if ($this->isName($variable, $expectedName)) {
                continue;
            }
            $currentName = $this->getName($variable);
            if ($currentName === null) {
                continue;
            }
            if ($this->breakingVariableRenameGuard->shouldSkipVariable($currentName, $expectedName, $node, $variable)) {
                continue;
            }
            $hasChanged = \true;
            // 1. rename assigned variable
            $assignOfNew->var = new \PhpParser\Node\Expr\Variable($expectedName);
            // 2. rename variable in the
            $this->variableRenamer->renameVariableInFunctionLike($node, $currentName, $expectedName, $assignOfNew);
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return Assign[]
     */
    private function getAssignsOfNew(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Expr\Assign::class);
        return \array_filter($assigns, function (\PhpParser\Node\Expr\Assign $assign) : bool {
            return $assign->expr instanceof \PhpParser\Node\Expr\New_;
        });
    }
}
