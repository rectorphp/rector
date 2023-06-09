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
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeAnalyzer\ExprInTopStmtMatcher;
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
     * @var \Rector\NodeAnalyzer\ExprInTopStmtMatcher
     */
    private $exprInTopStmtMatcher;
    public function __construct(VariableNaming $variableNaming, ExprInTopStmtMatcher $exprInTopStmtMatcher)
    {
        $this->variableNaming = $variableNaming;
        $this->exprInTopStmtMatcher = $exprInTopStmtMatcher;
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
        return [StmtsAwareInterface::class, Switch_::class, Return_::class, Expression::class, Echo_::class];
    }
    /**
     * @param StmtsAwareInterface|Switch_|Return_|Expression|Echo_ $node
     * @return Node[]|null
     */
    public function refactor(Node $node) : ?array
    {
        $exprArrayKeyFirst = $this->exprInTopStmtMatcher->match($node, function (Node $subNode) : bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            return $this->isName($subNode, 'array_key_first');
        });
        if ($exprArrayKeyFirst instanceof FuncCall) {
            return $this->refactorArrayKeyFirst($exprArrayKeyFirst, $node);
        }
        $exprArrayKeyLast = $this->exprInTopStmtMatcher->match($node, function (Node $subNode) : bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            return $this->isName($subNode, 'array_key_last');
        });
        if ($exprArrayKeyLast instanceof FuncCall) {
            return $this->refactorArrayKeyLast($exprArrayKeyLast, $node);
        }
        return null;
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
     * @return Node[]|null
     * @param \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    private function refactorArrayKeyFirst(FuncCall $funcCall, $stmt) : ?array
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
        $newStmts[] = $resetFuncCallExpression;
        $newStmts[] = $stmt;
        return $newStmts;
    }
    /**
     * @return Node[]|null
     * @param \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    private function refactorArrayKeyLast(FuncCall $funcCall, $stmt) : ?array
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
