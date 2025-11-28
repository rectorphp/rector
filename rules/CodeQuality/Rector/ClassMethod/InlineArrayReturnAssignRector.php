<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\CodeQuality\NodeAnalyzer\VariableDimFetchAssignResolver;
use Rector\Exception\NotImplementedYetException;
use Rector\PhpParser\Enum\NodeGroup;
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
     */
    private VariableDimFetchAssignResolver $variableDimFetchAssignResolver;
    public function __construct(VariableDimFetchAssignResolver $variableDimFetchAssignResolver)
    {
        $this->variableDimFetchAssignResolver = $variableDimFetchAssignResolver;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $stmts = $node->stmts;
        // skip primitive cases, as may be on purpose
        if (count($stmts) < 3) {
            return null;
        }
        $lastStmt = $node->stmts[count($stmts) - 1] ?? null;
        if (!$lastStmt instanceof Return_) {
            return null;
        }
        if (!$lastStmt->expr instanceof Variable) {
            return null;
        }
        $returnedVariableName = $this->getName($lastStmt->expr);
        if (!is_string($returnedVariableName)) {
            return null;
        }
        if ($node instanceof FunctionLike) {
            foreach ($node->getParams() as $param) {
                if ($this->isName($param->var, $returnedVariableName)) {
                    return null;
                }
            }
            if ($node instanceof Closure) {
                foreach ($node->uses as $use) {
                    if (!$use->byRef) {
                        continue;
                    }
                    if ($this->isName($use->var, $returnedVariableName)) {
                        return null;
                    }
                }
            }
        }
        $emptyArrayAssign = $this->resolveDefaultEmptyArrayAssign($stmts, $returnedVariableName);
        if (!$this->areAssignExclusiveToDimFetchVariable($stmts, $emptyArrayAssign, $returnedVariableName)) {
            return null;
        }
        // init maybe from before if
        if (!$emptyArrayAssign instanceof Assign && !$node instanceof FunctionLike) {
            return null;
        }
        try {
            $keysAndExprsByKey = $this->variableDimFetchAssignResolver->resolveFromStmtsAndVariable($stmts, $emptyArrayAssign);
        } catch (NotImplementedYetException $exception) {
            // dim fetch assign of nested arrays is hard to resolve
            return null;
        }
        if ($keysAndExprsByKey === []) {
            return null;
        }
        $array = $this->nodeFactory->createArray($keysAndExprsByKey);
        $node->stmts = [new Return_($array)];
        return $node;
    }
    /**
     * Only:
     * $items['...'] = $result;
     *
     * @param Stmt[] $stmts
     */
    private function areAssignExclusiveToDimFetchVariable(array $stmts, ?Assign $emptyArrayAssign, string $variableName): bool
    {
        $lastKey = array_key_last($stmts);
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
            // skip initial assign
            if ($assign === $emptyArrayAssign) {
                continue;
            }
            // skip new X instance with args to keep complex assign readable
            if ($assign->expr instanceof New_ && !$assign->expr->isFirstClassCallable() && $assign->expr->getArgs() !== []) {
                return \false;
            }
            if (!$assign->var instanceof ArrayDimFetch) {
                return \false;
            }
            $arrayDimFetch = $assign->var;
            // traverse all nested variables up
            while ($arrayDimFetch instanceof ArrayDimFetch) {
                $arrayDimFetch = $arrayDimFetch->var;
            }
            if (!$arrayDimFetch instanceof Variable) {
                return \false;
            }
            if (!$this->isName($arrayDimFetch, $variableName)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveDefaultEmptyArrayAssign(array $stmts, string $returnedVariableName): ?Assign
    {
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            if (!$this->isName($assign->var, $returnedVariableName)) {
                continue;
            }
            if (!$assign->expr instanceof Array_) {
                continue;
            }
            if ($assign->expr->items !== []) {
                continue;
            }
            return $assign;
        }
        return null;
    }
}
