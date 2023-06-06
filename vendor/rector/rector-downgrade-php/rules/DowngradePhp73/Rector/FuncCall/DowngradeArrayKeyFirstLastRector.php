<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeAnalyzer\StmtMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/array_key_first_last
 *
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\DowngradeArrayKeyFirstLastRectorTest
 */
final class DowngradeArrayKeyFirstLastRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\StmtMatcher
     */
    private $stmtMatcher;
    public function __construct(VariableNaming $variableNaming, StmtMatcher $stmtMatcher)
    {
        $this->variableNaming = $variableNaming;
        $this->stmtMatcher = $stmtMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade array_key_first() and array_key_last() functions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $firstItemKey = array_key_first($items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        reset($items);
        $firstItemKey = key($items);
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
        return [Expression::class, If_::class, ElseIf_::class, Else_::class];
    }
    /**
     * @param Expression|If_|Stmt\Else_|Stmt\ElseIf_ $node
     * @return Stmt[]|StmtsAwareInterface|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_ || $node instanceof ElseIf_) {
            $scopeStmt = \array_merge([$node->cond], $node->stmts);
        } elseif ($node instanceof Else_) {
            $scopeStmt = $node->stmts;
        } else {
            $scopeStmt = $node;
        }
        $isPartOfCond = \false;
        if ($node instanceof If_ || $node instanceof ElseIf_) {
            $isPartOfCond = $this->stmtMatcher->matchFuncCallNamed([$node->cond], 'array_key_first') || $this->stmtMatcher->matchFuncCallNamed([$node->cond], 'array_key_last');
        }
        $funcCall = $this->stmtMatcher->matchFuncCallNamed($scopeStmt, 'array_key_first');
        if ($funcCall instanceof FuncCall) {
            return $this->refactorArrayKeyFirst($funcCall, $node, $isPartOfCond);
        }
        $funcCall = $this->stmtMatcher->matchFuncCallNamed($scopeStmt, 'array_key_last');
        if ($funcCall instanceof FuncCall) {
            return $this->refactorArrayKeyLast($funcCall, $node, $isPartOfCond);
        }
        return null;
    }
    private function processInsertFuncCallExpression(StmtsAwareInterface $stmtsAware, Expression $expression, FuncCall $funcCall) : StmtsAwareInterface
    {
        if ($stmtsAware->stmts === null) {
            return $stmtsAware;
        }
        foreach ($stmtsAware->stmts as $key => $stmt) {
            $hasFuncCall = $this->betterNodeFinder->findFirst($stmt, static function (Node $node) use($funcCall) : bool {
                return $node === $funcCall;
            });
            if ($hasFuncCall instanceof Node) {
                \array_splice($stmtsAware->stmts, $key, 0, [$expression]);
                return $stmtsAware;
            }
        }
        return $stmtsAware;
    }
    private function resolveVariableFromCallLikeScope(CallLike $callLike, ?Scope $scope) : Variable
    {
        /** @var MethodCall|FuncCall|StaticCall|New_|NullsafeMethodCall $callLike */
        if ($callLike instanceof New_) {
            $variableName = (string) $this->nodeNameResolver->getName($callLike->class);
        } else {
            $variableName = (string) $this->nodeNameResolver->getName($callLike->name);
        }
        if ($variableName === '') {
            $variableName = 'array';
        }
        return new Variable($this->variableNaming->createCountedValueName($variableName, $scope));
    }
    /**
     * @return Stmt[]|StmtsAwareInterface|null
     * @param \PhpParser\Node\Stmt\Expression|\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmt
     */
    private function refactorArrayKeyFirst(FuncCall $funcCall, $stmt, bool $isPartOfCond)
    {
        $args = $funcCall->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $originalArray = $args[0]->value;
        $array = $this->resolveCastedArray($originalArray);
        $newStmts = [];
        if ($originalArray instanceof CallLike) {
            $scope = $originalArray->getAttribute(AttributeKey::SCOPE);
            $array = $this->resolveVariableFromCallLikeScope($originalArray, $scope);
        }
        if ($originalArray !== $array) {
            $newStmts[] = new Expression(new Assign($array, $originalArray));
        }
        $resetFuncCall = $this->nodeFactory->createFuncCall('reset', [$array]);
        $resetFuncCallExpression = new Expression($resetFuncCall);
        $funcCall->name = new Name('key');
        if ($originalArray !== $array) {
            $firstArg = $args[0];
            $firstArg->value = $array;
        }
        if ($stmt instanceof StmtsAwareInterface && $isPartOfCond === \false) {
            return $this->processInsertFuncCallExpression($stmt, $resetFuncCallExpression, $funcCall);
        }
        $newStmts[] = $resetFuncCallExpression;
        $newStmts[] = $stmt;
        return $newStmts;
    }
    /**
     * @return Stmt[]|StmtsAwareInterface|null
     * @param \PhpParser\Node\Stmt\Expression|\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmt
     */
    private function refactorArrayKeyLast(FuncCall $funcCall, $stmt, bool $isPartOfCond)
    {
        $args = $funcCall->getArgs();
        $firstArg = $args[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $originalArray = $firstArg->value;
        $array = $this->resolveCastedArray($originalArray);
        $newStmts = [];
        if ($originalArray instanceof CallLike) {
            $scope = $originalArray->getAttribute(AttributeKey::SCOPE);
            $array = $this->resolveVariableFromCallLikeScope($originalArray, $scope);
        }
        if ($originalArray !== $array) {
            $newStmts[] = new Expression(new Assign($array, $originalArray));
        }
        $endFuncCall = $this->nodeFactory->createFuncCall('end', [$array]);
        $endFuncCallExpression = new Expression($endFuncCall);
        $newStmts[] = $endFuncCallExpression;
        $funcCall->name = new Name('key');
        if ($originalArray !== $array) {
            $firstArg->value = $array;
        }
        if ($stmt instanceof StmtsAwareInterface && $isPartOfCond === \false) {
            return $this->processInsertFuncCallExpression($stmt, $endFuncCallExpression, $funcCall);
        }
        $newStmts[] = $stmt;
        return $newStmts;
    }
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable
     */
    private function resolveCastedArray(Expr $expr)
    {
        if (!$expr instanceof Array_) {
            return $expr;
        }
        if ($expr->expr instanceof Array_) {
            return $this->resolveCastedArray($expr->expr);
        }
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        $variableName = $this->variableNaming->createCountedValueName((string) $this->nodeNameResolver->getName($expr->expr), $scope);
        return new Variable($variableName);
    }
}
