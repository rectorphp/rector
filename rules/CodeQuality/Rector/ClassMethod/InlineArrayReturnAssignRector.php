<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\CodeQuality\NodeAnalyzer\VariableDimFetchAssignResolver;
use Rector\CodeQuality\ValueObject\KeyAndExpr;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\InlineArrayReturnAssignRectorTest
 */
final class InlineArrayReturnAssignRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\VariableDimFetchAssignResolver
     */
    private $variableDimFetchAssignResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(VariableDimFetchAssignResolver $variableDimFetchAssignResolver, ValueResolver $valueResolver)
    {
        $this->variableDimFetchAssignResolver = $variableDimFetchAssignResolver;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Inline just in time array dim fetch assigns to direct return', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stmts = (array) $node->stmts;
        if (\count($stmts) < 3) {
            return null;
        }
        $firstStmt = \array_shift($stmts);
        $variable = $this->matchVariableAssignOfEmptyArray($firstStmt);
        if (!$variable instanceof Variable) {
            return null;
        }
        if (!$this->areAssignExclusiveToDimFetch($stmts)) {
            return null;
        }
        $lastStmt = \array_pop($stmts);
        if (!$lastStmt instanceof Stmt) {
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
        $node->stmts = [new Return_($array)];
        return $node;
    }
    private function matchVariableAssignOfEmptyArray(Stmt $stmt) : ?Variable
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$this->valueResolver->isValue($assign->expr, [])) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        return $assign->var;
    }
    private function isReturnOfVariable(Stmt $stmt, Variable $variable) : bool
    {
        if (!$stmt instanceof Return_) {
            return \false;
        }
        if (!$stmt->expr instanceof Variable) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($stmt->expr, $variable);
    }
    /**
     * @param KeyAndExpr[] $keysAndExprs
     */
    private function createArray(array $keysAndExprs) : Array_
    {
        $arrayItems = [];
        foreach ($keysAndExprs as $keyAndExpr) {
            $arrayItem = new ArrayItem($keyAndExpr->getExpr(), $keyAndExpr->getKeyExpr());
            $arrayItem->setAttribute(AttributeKey::COMMENTS, $keyAndExpr->getComments());
            $arrayItems[] = $arrayItem;
        }
        return new Array_($arrayItems);
    }
    /**
     * Only:
     * $items['...'] = $result;
     *
     * @param Stmt[] $stmts
     */
    private function areAssignExclusiveToDimFetch(array $stmts) : bool
    {
        \end($stmts);
        $lastKey = \key($stmts);
        \reset($stmts);
        foreach ($stmts as $key => $stmt) {
            if ($key === $lastKey) {
                // skip last item
                continue;
            }
            if (!$stmt instanceof Expression) {
                return \false;
            }
            if (!$stmt->expr instanceof Assign) {
                return \false;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof ArrayDimFetch) {
                return \false;
            }
        }
        return \true;
    }
}
