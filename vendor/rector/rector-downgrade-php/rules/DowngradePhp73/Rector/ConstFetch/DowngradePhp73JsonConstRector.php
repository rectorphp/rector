<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as NameFullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\VariadicPlaceholder;
use PhpParser\NodeVisitor;
use Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner;
use Rector\Enum\JsonConstant;
use Rector\NodeAnalyzer\DefineFuncCallAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.json-encode.php#refsect1-function.json-encode-changelog
 *
 * @see \Rector\Tests\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector\DowngradePhp73JsonConstRectorTest
 */
final class DowngradePhp73JsonConstRector extends AbstractRector
{
    /**
     * @readonly
     */
    private JsonConstCleaner $jsonConstCleaner;
    /**
     * @readonly
     */
    private DefineFuncCallAnalyzer $defineFuncCallAnalyzer;
    /**
     * @var string
     */
    private const PHP73_JSON_CONSTANT_IS_KNOWN = 'php73_json_constant_is_known';
    /**
     * @var array<string>
     */
    private const REFACTOR_FUNCS = ['json_decode', 'json_encode'];
    /**
     * @var string
     */
    private const IS_EXPRESSION_INSIDE_TRY_CATCH = 'is_expression_inside_try_catch';
    public function __construct(JsonConstCleaner $jsonConstCleaner, DefineFuncCallAnalyzer $defineFuncCallAnalyzer)
    {
        $this->jsonConstCleaner = $jsonConstCleaner;
        $this->defineFuncCallAnalyzer = $defineFuncCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove Json constant that available only in php 7.3', [new CodeSample(<<<'CODE_SAMPLE'
json_encode($content, JSON_THROW_ON_ERROR);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
json_encode($content, 0);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new \Exception(json_last_error_msg());
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
$content = json_decode($json, null, 512, JSON_THROW_ON_ERROR);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$content = json_decode($json, null, 512, 0);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new \Exception(json_last_error_msg());
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ConstFetch::class, BitwiseOr::class, If_::class, TryCatch::class, Expression::class];
    }
    /**
     * @param ConstFetch|BitwiseOr|If_|TryCatch|Expression $node
     * @return null|Expr|array<Expression|If_>
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_) {
            $this->markConstantKnownInInnerStmts($node);
            return null;
        }
        // skip as known
        if ((bool) $node->getAttribute(self::PHP73_JSON_CONSTANT_IS_KNOWN)) {
            return null;
        }
        if ($node instanceof TryCatch) {
            $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) : ?int {
                if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                if (!$subNode instanceof Expression) {
                    return null;
                }
                $funcCall = $this->resolveFuncCall($subNode);
                if ($funcCall instanceof FuncCall) {
                    $subNode->setAttribute(self::IS_EXPRESSION_INSIDE_TRY_CATCH, \true);
                }
                return null;
            });
            return null;
        }
        if ($node instanceof Expression) {
            return $this->refactorStmt($node);
        }
        return $this->jsonConstCleaner->clean($node, [JsonConstant::THROW_ON_ERROR]);
    }
    private function markConstantKnownInInnerStmts(If_ $if) : void
    {
        if (!$this->defineFuncCallAnalyzer->isDefinedWithConstants($if->cond, [JsonConstant::THROW_ON_ERROR])) {
            return;
        }
        $this->traverseNodesWithCallable($if, static function (Node $node) {
            $node->setAttribute(self::PHP73_JSON_CONSTANT_IS_KNOWN, \true);
            return null;
        });
    }
    private function resolveFuncCall(Expression $Expression) : ?FuncCall
    {
        $expr = $Expression->expr;
        if ($expr instanceof Assign) {
            if ($expr->expr instanceof FuncCall) {
                return $expr->expr;
            }
            return null;
        }
        if ($expr instanceof FuncCall) {
            return $expr;
        }
        return null;
    }
    /**
     * Add an alternative throwing error behavior after any `json_encode`
     * or `json_decode` function called with the `JSON_THROW_ON_ERROR` flag set.
     * This is a partial improvement of removing the `JSON_THROW_ON_ERROR` flag,
     * only when the flags are directly set in the function call.
     * If the flags are set from a variable, that would require a much more
     * complex analysis to be 100% accurate, beyond Rector actual capabilities.
     * @return null|array<Expression|If_>
     */
    private function refactorStmt(Expression $Expression) : ?array
    {
        if ($Expression->getAttribute(self::IS_EXPRESSION_INSIDE_TRY_CATCH) === \true) {
            return null;
        }
        // retrieve a `FuncCall`, if any, from the statement
        $funcCall = $this->resolveFuncCall($Expression);
        // Nothing to do if no `FuncCall` found
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        // Nothing to do if not a refactored function
        if (!\in_array($this->getName($funcCall), self::REFACTOR_FUNCS, \true)) {
            return null;
        }
        // Nothing to do if the flag `JSON_THROW_ON_ERROR` is not set in args
        if (!$this->hasConstFetchInArgs($funcCall->args, 'JSON_THROW_ON_ERROR')) {
            return null;
        }
        $nodes = [$Expression];
        $nodes[] = new If_(new NotIdentical(new FuncCall(new Name('json_last_error')), new ConstFetch(new Name('JSON_ERROR_NONE'))), ['stmts' => [new Expression(new Throw_(new New_(new NameFullyQualified('Exception'), [new Arg(new FuncCall(new Name('json_last_error_msg')))])))]]);
        return $nodes;
    }
    /**
     * Search if a given constant is set within a list of `Arg`
     * @param array<Arg|VariadicPlaceholder> $args
     */
    private function hasConstFetchInArgs(array $args, string $constName) : bool
    {
        foreach ($args as $arg) {
            // Only `Arg` instances are handled.
            if (!$arg instanceof Arg) {
                return \false;
            }
            $value = $arg->value;
            if ($value instanceof ConstFetch && $this->getName($value) === $constName) {
                return \true;
            }
            if ($value instanceof BitwiseOr) {
                return $this->hasConstFetchInBitwiseOr($value, $constName);
            }
        }
        return \false;
    }
    /**
     * Search if a given constant is set within a `BitwiseOr`
     */
    private function hasConstFetchInBitwiseOr(BitwiseOr $bitwiseOr, string $constName) : bool
    {
        $found = \false;
        foreach ([$bitwiseOr->left, $bitwiseOr->right] as $subNode) {
            switch (\true) {
                case $subNode instanceof BitwiseOr:
                    $found = $this->hasConstFetchInBitwiseOr($subNode, $constName);
                    break;
                case $subNode instanceof ConstFetch:
                    $found = $this->getName($subNode) === $constName;
                    break;
                default:
                    $found = \false;
                    break;
            }
            if ($found) {
                break;
            }
        }
        return $found;
    }
}
