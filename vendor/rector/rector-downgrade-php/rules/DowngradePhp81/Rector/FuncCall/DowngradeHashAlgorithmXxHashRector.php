<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\If_;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHash\DowngradeHashAlgorithmXxHashRectorTest
 */
final class DowngradeHashAlgorithmXxHashRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * Constants are referenced by name, as the MHASH_* constants are deprecated since PHP 8.5
     *
     * @var array<string, string>
     */
    private const HASH_ALGORITHMS_TO_DOWNGRADE = ['xxh32' => 'MHASH_XXH32', 'xxh64' => 'MHASH_XXH64', 'xxh3' => 'MHASH_XXH3', 'xxh128' => 'MHASH_XXH128'];
    /**
     * @var string
     */
    private const REPLACEMENT_ALGORITHM = 'md5';
    private int $argNamedKey;
    public function __construct(ArgsAnalyzer $argsAnalyzer, ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade hash algorithm xxh32, xxh64, xxh3 or xxh128 by default to md5. You can configure the algorithm to downgrade.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return hash('xxh128', 'some-data-to-hash');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return hash('md5', 'some-data-to-hash');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class, If_::class, FuncCall::class];
    }
    /**
     * @param Ternary|If_|FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if ($node instanceof Ternary || $node instanceof If_) {
            $this->markGuardedHashCalls($node);
            return null;
        }
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($node->getAttribute(AttributeKey::PHP_VERSION_CONDITIONED)) {
            return null;
        }
        $this->argNamedKey = 0;
        $algorithm = $this->getHashAlgorithm($node->getArgs());
        if ($algorithm === null || !array_key_exists($algorithm, self::HASH_ALGORITHMS_TO_DOWNGRADE)) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[$this->argNamedKey])) {
            return null;
        }
        $arg = $args[$this->argNamedKey];
        $arg->value = new String_(self::REPLACEMENT_ALGORITHM);
        return $node;
    }
    private function shouldSkip(FuncCall $funcCall): bool
    {
        if ($funcCall->isFirstClassCallable()) {
            return \true;
        }
        return !$this->isName($funcCall, 'hash');
    }
    /**
     * Mark hash() calls guarded by a PHP_VERSION_ID check as version conditioned,
     * so they are skipped like version_compare() guarded calls.
     * @param \PhpParser\Node\Expr\Ternary|\PhpParser\Node\Stmt\If_ $node
     */
    private function markGuardedHashCalls($node): void
    {
        if (!$this->hasPhpVersionIdCond($node->cond)) {
            return;
        }
        /** @var FuncCall[] $funcCalls */
        $funcCalls = $this->betterNodeFinder->findInstancesOf($node, [FuncCall::class]);
        foreach ($funcCalls as $funcCall) {
            if (!$this->isName($funcCall, 'hash')) {
                continue;
            }
            $funcCall->setAttribute(AttributeKey::PHP_VERSION_CONDITIONED, \true);
        }
    }
    private function hasPhpVersionIdCond(Expr $expr): bool
    {
        if (!$expr instanceof BinaryOp) {
            return \false;
        }
        return $this->isPhpVersionIdConstFetch($expr->left) || $this->isPhpVersionIdConstFetch($expr->right);
    }
    private function isPhpVersionIdConstFetch(Expr $expr): bool
    {
        return $expr instanceof ConstFetch && $this->isName($expr, 'PHP_VERSION_ID');
    }
    /**
     * @param Arg[] $args
     */
    private function getHashAlgorithm(array $args): ?string
    {
        $arg = null;
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            foreach ($args as $key => $arg) {
                if ((($nullsafeVariable1 = $arg->name) ? $nullsafeVariable1->name : null) !== 'algo') {
                    continue;
                }
                $this->argNamedKey = $key;
                break;
            }
        } else {
            $arg = $args[$this->argNamedKey];
        }
        $algorithmNode = ($nullsafeVariable2 = $arg) ? $nullsafeVariable2->value : null;
        switch (\true) {
            case $algorithmNode instanceof String_:
                return $this->valueResolver->getValue($algorithmNode);
            case $algorithmNode instanceof ConstFetch:
                return $this->mapConstantToString($this->valueResolver->getValue($algorithmNode));
            default:
                return null;
        }
    }
    private function mapConstantToString(string $constant): string
    {
        $mappedConstant = array_search(ltrim($constant, '\\'), self::HASH_ALGORITHMS_TO_DOWNGRADE, \true);
        return $mappedConstant !== \false ? $mappedConstant : $constant;
    }
}
