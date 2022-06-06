<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\While_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\ValueObject\PropertyFetchToVariableAssign;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\RemoveJustPropertyFetchRectorTest
 */
final class RemoveJustPropertyFetchRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\ReadWrite\NodeFinder\NodeUsageFinder
     */
    private $nodeUsageFinder;
    public function __construct(\Rector\ReadWrite\NodeFinder\NodeUsageFinder $nodeUsageFinder)
    {
        $this->nodeUsageFinder = $nodeUsageFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Inline property fetch assign to a variable, that has no added value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $name;

    public function run()
    {
        $name = $this->name;

        return $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $name;

    public function run()
    {
        return $this->name;
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
        return [\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $stmts = (array) $node->stmts;
        if ($stmts === []) {
            return null;
        }
        $variableUsages = [];
        $currentStmtKey = null;
        $variableToPropertyAssign = null;
        foreach ($stmts as $key => $stmt) {
            $variableToPropertyAssign = $this->matchVariableToPropertyAssign($stmt);
            if (!$variableToPropertyAssign instanceof \Rector\DeadCode\ValueObject\PropertyFetchToVariableAssign) {
                continue;
            }
            $assignPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($stmt);
            // there is a @var tag on purpose, keep the assign
            if ($assignPhpDocInfo->getVarTagValueNode() instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
                continue;
            }
            $followingStmts = \array_slice($stmts, $key + 1);
            $variableUsages = $this->nodeUsageFinder->findVariableUsages($followingStmts, $variableToPropertyAssign->getVariable());
            $currentStmtKey = $key;
            // @todo validate the variable is not used in some place where property fetch cannot be used
            break;
        }
        // filter out variable usages that are part of nested property fetch, or change variable
        $variableUsages = $this->filterOutReferencedVariableUsages($variableUsages);
        if (!$variableToPropertyAssign instanceof \Rector\DeadCode\ValueObject\PropertyFetchToVariableAssign) {
            return null;
        }
        if ($variableUsages === []) {
            return null;
        }
        /** @var int $currentStmtKey */
        return $this->replaceVariablesWithPropertyFetch($node, $currentStmtKey, $variableUsages, $variableToPropertyAssign->getPropertyFetch());
    }
    /**
     * @param Variable[] $variableUsages
     */
    private function replaceVariablesWithPropertyFetch(\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmtsAware, int $currentStmtsKey, array $variableUsages, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface
    {
        // remove assign node
        unset($stmtsAware->stmts[$currentStmtsKey]);
        $this->traverseNodesWithCallable($stmtsAware, function (\PhpParser\Node $node) use($variableUsages, $propertyFetch) : ?PropertyFetch {
            if (!\in_array($node, $variableUsages, \true)) {
                return null;
            }
            return $propertyFetch;
        });
        return $stmtsAware;
    }
    /**
     * @param Variable[] $variableUsages
     * @return Variable[]
     */
    private function filterOutReferencedVariableUsages(array $variableUsages) : array
    {
        return \array_filter($variableUsages, function (\PhpParser\Node\Expr\Variable $variable) : bool {
            $variableUsageParent = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($variableUsageParent instanceof \PhpParser\Node\Arg) {
                $variableUsageParent = $variableUsageParent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            }
            // skip nested property fetch, the assign is for purpose of named variable
            if ($variableUsageParent instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return \false;
            }
            // skip, as assign can be used in a loop
            $parentWhile = $this->betterNodeFinder->findParentType($variable, \PhpParser\Node\Stmt\While_::class);
            if ($parentWhile instanceof \PhpParser\Node\Stmt\While_) {
                return \false;
            }
            if (!$variableUsageParent instanceof \PhpParser\Node\Expr\FuncCall) {
                return \true;
            }
            return !$this->isName($variableUsageParent, 'array_pop');
        });
    }
    private function matchVariableToPropertyAssign(\PhpParser\Node\Stmt $stmt) : ?\Rector\DeadCode\ValueObject\PropertyFetchToVariableAssign
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if ($this->isPropertyFetchCallerNode($assign->expr)) {
            return null;
        }
        // keep property fetch nesting
        if ($assign->expr->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        return new \Rector\DeadCode\ValueObject\PropertyFetchToVariableAssign($assign->var, $assign->expr);
    }
    private function isPropertyFetchCallerNode(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        // skip nodes as mostly used with public property fetches
        $propertyFetchCallerType = $this->getType($propertyFetch->var);
        if (!$propertyFetchCallerType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        $nodeObjectType = new \PHPStan\Type\ObjectType('PhpParser\\Node');
        return $nodeObjectType->isSuperTypeOf($propertyFetchCallerType)->yes();
    }
}
