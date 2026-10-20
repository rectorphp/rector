<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @changelog https://www.php.net/manual/en/migration82.other-changes.php#migration82.other-changes.functions.spl
 *
 * @see \Rector\Tests\DowngradePhp82\Rector\FuncCall\DowngradeIteratorCountToArrayRector\DowngradeIteratorCountToArrayRectorTest
 * @see https://3v4l.org/TPW7a#v8.1.31
 */
final class DowngradeIteratorCountToArrayRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @var string
     */
    private const SKIP_NODE = 'skip_node';
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class, FuncCall::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Ensure pass Traversable instance before use in iterator_count() and iterator_to_array()', [new CodeSample(<<<'CODE_SAMPLE'
function test(array|Traversable $data) {
    $c = iterator_count($data);
    $v = iterator_to_array($data);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function test(array|Traversable $data) {
    $c = iterator_count(is_array($data) ? new ArrayIterator($data) : $data);
    $v = iterator_to_array(is_array($data) ? new ArrayIterator($data) : $data);
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Ternary|FuncCall $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Expr\FuncCall
    {
        if ($node instanceof Ternary) {
            $this->markIsArrayGuardedCall($node);
            return null;
        }
        if ($node->getAttribute(self::SKIP_NODE) === \true) {
            return null;
        }
        if (!$this->isNames($node, ['iterator_count', 'iterator_to_array'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $arg = $node->getArg('iterator', 0);
        if (!$arg instanceof Arg) {
            return null;
        }
        // already wrapped in a previous pass; the wrap type stays not-yes-object (e.g. ArrayIterator|mixed),
        // so without this check it would be wrapped again on every pass
        if ($this->isArrayIteratorWrapped($arg->value)) {
            return null;
        }
        $type = $this->nodeTypeResolver->getType($arg->value);
        if ($this->shouldSkip($type)) {
            return null;
        }
        $position = $this->argsAnalyzer->resolveArgPosition($node->getArgs(), 'iterator', 0);
        Assert::isInstanceOf($node->args[$position], Arg::class);
        $firstValue = $node->args[$position]->value;
        $node->args[$position]->value = new Ternary($this->nodeFactory->createFuncCall('is_array', [new Arg($firstValue)]), new New_(new FullyQualified('ArrayIterator'), [new Arg($firstValue)]), $firstValue);
        return $node;
    }
    /**
     * Skip the not-array branch of an is_array() check on the same expr, e.g.
     * is_array($data) ? $data : iterator_to_array($data)
     */
    private function markIsArrayGuardedCall(Ternary $ternary): void
    {
        $cond = $ternary->cond;
        $notArrayBranch = $ternary->else;
        if ($cond instanceof BooleanNot) {
            $cond = $cond->expr;
            $notArrayBranch = $ternary->if;
        }
        if (!$cond instanceof FuncCall || !$this->isName($cond, 'is_array') || $cond->isFirstClassCallable()) {
            return;
        }
        if (!$notArrayBranch instanceof FuncCall || $notArrayBranch->isFirstClassCallable()) {
            return;
        }
        if (!$this->isNames($notArrayBranch, ['iterator_count', 'iterator_to_array'])) {
            return;
        }
        $checkedArg = $cond->getArg('value', 0);
        $iteratorArg = $notArrayBranch->getArg('iterator', 0);
        if (!$checkedArg instanceof Arg || !$iteratorArg instanceof Arg) {
            return;
        }
        if ($this->nodeComparator->areNodesEqual($checkedArg->value, $iteratorArg->value)) {
            $notArrayBranch->setAttribute(self::SKIP_NODE, \true);
        }
    }
    private function isArrayIteratorWrapped(Expr $expr): bool
    {
        if (!$expr instanceof Ternary) {
            return \false;
        }
        if (!$expr->cond instanceof FuncCall || !$this->isName($expr->cond, 'is_array')) {
            return \false;
        }
        if (!$expr->if instanceof New_) {
            return \false;
        }
        return $this->isName($expr->if->class, 'ArrayIterator');
    }
    private function shouldSkip(Type $type): bool
    {
        if ($type->isArray()->yes()) {
            return \false;
        }
        // never an array, e.g. narrowed by is_array($data) ? $data : iterator_to_array($data),
        // so the is_array() check would always be false
        if ($type->isArray()->no()) {
            return \true;
        }
        $type = TypeCombinator::removeNull($type);
        return $type->isObject()->yes();
    }
}
