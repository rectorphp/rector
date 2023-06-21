<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as NameFullyQualified;
use PhpParser\Node\Stmt\Expression as StmtExpression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner;
use Rector\Enum\JsonConstant;
use Rector\NodeAnalyzer\DefineFuncCallAnalyzer;
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
     * @var \Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner
     */
    private $jsonConstCleaner;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\DefineFuncCallAnalyzer
     */
    private $defineFuncCallAnalyzer;
    /**
     * @var string
     */
    private const PHP73_JSON_CONSTANT_IS_KNOWN = 'php73_json_constant_is_known';
    /**
     * @var array<string>
     */
    private const REFACTOR_FUNCS = ['json_decode', 'json_encode'];
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
        return [ConstFetch::class, BitwiseOr::class, If_::class, StmtExpression::class];
    }
    /**
     * @param ConstFetch|BitwiseOr|If_|StmtExpression $node
     * @return int|null|Expr|If_|array<StmtExpression|If_>
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_) {
            return $this->refactorIf($node);
        }
        // skip as known
        if ((bool) $node->getAttribute(self::PHP73_JSON_CONSTANT_IS_KNOWN)) {
            return null;
        }
        if ($node instanceof StmtExpression) {
            return $this->refactorStmt($node);
        }
        return $this->jsonConstCleaner->clean($node, [JsonConstant::THROW_ON_ERROR]);
    }
    private function refactorIf(If_ $if) : ?If_
    {
        if (!$this->defineFuncCallAnalyzer->isDefinedWithConstants($if->cond, [JsonConstant::THROW_ON_ERROR])) {
            return null;
        }
        $this->traverseNodesWithCallable($if, static function (Node $node) {
            $node->setAttribute(self::PHP73_JSON_CONSTANT_IS_KNOWN, \true);
            return null;
        });
        return $if;
    }
    private function resolveFuncCall(StmtExpression $stmtExpression) : ?FuncCall
    {
        $expr = $stmtExpression->expr;
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
     * @return null|array<StmtExpression|If_>
     */
    private function refactorStmt(StmtExpression $stmtExpression) : ?array
    {
        // retrieve a `FuncCall`, if any, from the statement
        $funcCall = $this->resolveFuncCall($stmtExpression);
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
        $nodes = [$stmtExpression];
        $nodes[] = new If_(new NotIdentical(new FuncCall(new Name('json_last_error')), new ConstFetch(new Name('JSON_ERROR_NONE'))), ['stmts' => [new Throw_(new New_(new NameFullyQualified('Exception'), [new Arg(new FuncCall(new Name('json_last_error_msg')))]))]]);
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
            // Only `Node` instances can hold the constant.
            if (!$subNode instanceof Node) {
                continue;
            }
            $found = \true === $subNode instanceof BitwiseOr ? $this->hasConstFetchInBitwiseOr($subNode, $constName) : (\true === $subNode instanceof ConstFetch ? $this->getName($subNode) === $constName : \false);
            if ($found) {
                break;
            }
        }
        return $found;
    }
}
