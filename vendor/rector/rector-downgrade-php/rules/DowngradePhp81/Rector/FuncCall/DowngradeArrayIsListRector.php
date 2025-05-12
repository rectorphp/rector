<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Analyser\Scope;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeAnalyzer\ExprInTopStmtMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Parser\InlineCodeParser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/is_list
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\DowngradeArrayIsListRectorTest
 */
final class DowngradeArrayIsListRector extends AbstractRector
{
    /**
     * @readonly
     */
    private InlineCodeParser $inlineCodeParser;
    /**
     * @readonly
     */
    private ExprInTopStmtMatcher $exprInTopStmtMatcher;
    private ?Closure $cachedClosure = null;
    public function __construct(InlineCodeParser $inlineCodeParser, ExprInTopStmtMatcher $exprInTopStmtMatcher)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->exprInTopStmtMatcher = $exprInTopStmtMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace array_is_list() function', [new CodeSample(<<<'CODE_SAMPLE'
array_is_list([1 => 'apple', 'orange']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$arrayIsList = function (array $array) : bool {
    if (function_exists('array_is_list')) {
        return array_is_list($array);
    }

    if ($array === []) {
        return true;
    }

    $current_key = 0;
    foreach ($array as $key => $noop) {
        if ($key !== $current_key) {
            return false;
        }
        ++$current_key;
    }

    return true;
};
$arrayIsList([1 => 'apple', 'orange']);
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
        $expr = $this->exprInTopStmtMatcher->match($node, function (Node $subNode) : bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            // need pull Scope from target traversed sub Node
            return !$this->shouldSkip($subNode);
        });
        if (!$expr instanceof FuncCall) {
            return null;
        }
        $variable = new Variable('arrayIsListFunction');
        $function = $this->createClosure();
        $expression = new Expression(new Assign($variable, $function));
        $expr->name = $variable;
        return [$expression, $node];
    }
    private function createClosure() : Closure
    {
        if ($this->cachedClosure instanceof Closure) {
            return clone $this->cachedClosure;
        }
        $stmts = $this->inlineCodeParser->parseFile(__DIR__ . '/../../snippet/array_is_list_closure.php.inc');
        /** @var Expression $expression */
        $expression = $stmts[0];
        $expr = $expression->expr;
        if (!$expr instanceof Closure) {
            throw new ShouldNotHappenException();
        }
        $this->cachedClosure = $expr;
        return $expr;
    }
    private function shouldSkip(CallLike $callLike) : bool
    {
        if (!$callLike instanceof FuncCall) {
            return \false;
        }
        if (!$this->isName($callLike, 'array_is_list')) {
            return \true;
        }
        $scope = $callLike->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope && $scope->isInFunctionExists('array_is_list')) {
            return \true;
        }
        $args = $callLike->getArgs();
        return \count($args) !== 1;
    }
}
