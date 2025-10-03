<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\Type\IntegerRangeType;
use Rector\DeadCode\ConditionResolver;
use Rector\DeadCode\ValueObject\VersionCompareCondition;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
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
    private ConditionResolver $conditionResolver;
    private const HASH_ALGORITHMS_TO_DOWNGRADE = ['xxh32' => \MHASH_XXH32, 'xxh64' => \MHASH_XXH64, 'xxh3' => \MHASH_XXH3, 'xxh128' => \MHASH_XXH128];
    private const REPLACEMENT_ALGORITHM = 'md5';
    private int $argNamedKey;
    public function __construct(ArgsAnalyzer $argsAnalyzer, ValueResolver $valueResolver, ConditionResolver $conditionResolver)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->conditionResolver = $conditionResolver;
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
        return [If_::class, Ternary::class, FuncCall::class];
    }
    /**
     * @param If_|Ternary|FuncCall $node
     * @return null|NodeVisitor::DONT_TRAVERSE_CHILDREN|Node
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_) {
            if ($this->isVersionCompareIf($node)) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
            return null;
        }
        if ($node instanceof Ternary) {
            if ($this->isVersionCompareTernary($node)) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
            return null;
        }
        if ($this->shouldSkip($node)) {
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
    private function isVersionCompareIf(If_ $if): bool
    {
        if ($if->cond instanceof FuncCall) {
            // per use case reported only
            if (count($if->stmts) !== 1) {
                return \false;
            }
            $versionCompare = $this->conditionResolver->resolveFromExpr($if->cond);
            if (!$versionCompare instanceof VersionCompareCondition || $versionCompare->getSecondVersion() !== 80100) {
                return \false;
            }
            if ($versionCompare->getCompareSign() !== '>=') {
                return \false;
            }
            if ($if->stmts[0] instanceof Expression && $if->stmts[0]->expr instanceof Assign && $if->stmts[0]->expr->expr instanceof FuncCall) {
                return $this->isName($if->stmts[0]->expr->expr, 'hash');
            }
            if ($if->stmts[0] instanceof Return_ && $if->stmts[0]->expr instanceof FuncCall) {
                return $this->isName($if->stmts[0]->expr, 'hash');
            }
        }
        return \false;
    }
    private function isVersionCompareTernary(Ternary $ternary): bool
    {
        if ($ternary->if instanceof Expr && $ternary->cond instanceof FuncCall) {
            $versionCompare = $this->conditionResolver->resolveFromExpr($ternary->cond);
            if ($versionCompare instanceof VersionCompareCondition && $versionCompare->getSecondVersion() === 80100) {
                if ($versionCompare->getCompareSign() === '>=') {
                    return $ternary->if instanceof FuncCall && $this->isName($ternary->if, 'hash');
                }
                if ($versionCompare->getCompareSign() === '<') {
                    return $ternary->else instanceof FuncCall && $this->isName($ternary->else, 'hash');
                }
            }
        }
        return \false;
    }
    private function shouldSkip(FuncCall $funcCall): bool
    {
        if ($funcCall->isFirstClassCallable()) {
            return \true;
        }
        if (!$this->isName($funcCall, 'hash')) {
            return \true;
        }
        $scope = ScopeFetcher::fetch($funcCall);
        $type = $scope->getPhpVersion()->getType();
        if (!$type instanceof IntegerRangeType) {
            return \false;
        }
        return $type->getMin() === 80100;
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
        $mappedConstant = array_search(constant($constant), self::HASH_ALGORITHMS_TO_DOWNGRADE, \true);
        return $mappedConstant !== \false ? $mappedConstant : $constant;
    }
}
