<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\RemoveJustPropertyFetchRectorTest
 */
final class RemoveJustPropertyFetchRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Inline property fetch assign to a variable, that has no added value', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stmts = (array) $node->stmts;
        if ($stmts === []) {
            return null;
        }
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Variable) {
                continue;
            }
            $previousStmt = $stmts[$key - 1] ?? null;
            if (!$previousStmt instanceof Expression) {
                continue;
            }
            $propertyFetch = $this->matchVariableToPropertyFetchAssign($previousStmt, $stmt->expr);
            if (!$propertyFetch instanceof PropertyFetch) {
                continue;
            }
            $assignPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($previousStmt);
            // there is a @var tag on purpose, keep the assign
            if ($assignPhpDocInfo->getVarTagValueNode() instanceof VarTagValueNode) {
                continue;
            }
            unset($node->stmts[$key - 1]);
            $stmt->expr = $propertyFetch;
            return $node;
        }
        return null;
    }
    private function matchVariableToPropertyFetchAssign(Expression $expression, Variable $variable) : ?PropertyFetch
    {
        if (!$expression->expr instanceof Assign) {
            return null;
        }
        $assign = $expression->expr;
        if (!$assign->expr instanceof PropertyFetch) {
            return null;
        }
        // keep property fetch nesting
        if ($assign->expr->var instanceof PropertyFetch) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        if ($this->isPropertyFetchCallerNode($assign->expr)) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($variable, $assign->var)) {
            return null;
        }
        return $assign->expr;
    }
    private function isPropertyFetchCallerNode(PropertyFetch $propertyFetch) : bool
    {
        // skip nodes as mostly used with public property fetches
        $propertyFetchCallerType = $this->getType($propertyFetch->var);
        if (!$propertyFetchCallerType instanceof ObjectType) {
            return \false;
        }
        $nodeObjectType = new ObjectType('PhpParser\\Node');
        return $nodeObjectType->isSuperTypeOf($propertyFetchCallerType)->yes();
    }
}
