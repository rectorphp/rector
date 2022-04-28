<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\CodeQuality\NodeAnalyzer\VariableDimFetchAssignResolver;
use Rector\CodeQuality\NodeTypeGroup;
use Rector\CodeQuality\ValueObject\KeyAndExpr;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\InlineArrayReturnAssignRectorTest
 */
final class InlineArrayReturnAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\VariableDimFetchAssignResolver
     */
    private $variableDimFetchAssignResolver;
    public function __construct(\Rector\CodeQuality\NodeAnalyzer\VariableDimFetchAssignResolver $variableDimFetchAssignResolver)
    {
        $this->variableDimFetchAssignResolver = $variableDimFetchAssignResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Inline just in time array dim fetch assigns to direct return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function getPerson()
{
    $person = [];
    $person['name'] = 'Timmy';
    $person['surname'] = 'Back';

    return $person;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function getPerson()
{
    return [
        'name' => 'Timmy',
        'surname' => 'Back',
    ];
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return \Rector\CodeQuality\NodeTypeGroup::STMTS_AWARE;
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var Stmt[]|null $stmts */
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        if (\count($stmts) < 3) {
            return null;
        }
        $firstStmt = \array_shift($stmts);
        $variable = $this->matchVariableAssignOfEmptyArray($firstStmt);
        if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $lastStmt = \array_pop($stmts);
        if (!$lastStmt instanceof \PhpParser\Node\Stmt) {
            return null;
        }
        if (!$this->isReturnOfVariable($lastStmt, $variable)) {
            return null;
        }
        $keysAndExprs = $this->variableDimFetchAssignResolver->resolveFromStmtsAndVariable($stmts, $variable);
        if ($keysAndExprs === []) {
            return null;
        }
        $array = $this->createArray($keysAndExprs);
        $node->stmts = [new \PhpParser\Node\Stmt\Return_($array)];
        return $node;
    }
    private function matchVariableAssignOfEmptyArray(\PhpParser\Node\Stmt $stmt) : ?\PhpParser\Node\Expr\Variable
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$this->valueResolver->isValue($assign->expr, [])) {
            return null;
        }
        if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        return $assign->var;
    }
    private function isReturnOfVariable(\PhpParser\Node\Stmt $stmt, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($stmt->expr, $variable);
    }
    /**
     * @param KeyAndExpr[] $keysAndExprs
     */
    private function createArray(array $keysAndExprs) : \PhpParser\Node\Expr\Array_
    {
        $arrayItems = [];
        foreach ($keysAndExprs as $keyAndExpr) {
            $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem($keyAndExpr->getExpr(), $keyAndExpr->getKeyExpr());
        }
        return new \PhpParser\Node\Expr\Array_($arrayItems);
    }
}
