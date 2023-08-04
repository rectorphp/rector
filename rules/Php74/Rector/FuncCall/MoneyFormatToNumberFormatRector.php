<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.money-format.php#warning
 *
 * @see \Rector\Tests\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector\MoneyFormatToNumberFormatRectorTest
 */
final class MoneyFormatToNumberFormatRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_MONEY_FORMAT;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change money_format() to equivalent number_format()', [new CodeSample(<<<'CODE_SAMPLE'
$value = money_format('%i', $value);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = number_format(round($value, 2, PHP_ROUND_HALF_ODD), 2, '.', '');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if (!$this->isName($node, 'money_format')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        $formatValue = $args[0]->value;
        if (!$this->valueResolver->isValue($formatValue, '%i')) {
            return null;
        }
        return $this->warpInNumberFormatFuncCall($node, $args[1]->value);
    }
    private function warpInNumberFormatFuncCall(FuncCall $funcCall, Expr $expr) : FuncCall
    {
        $roundFuncCall = $this->nodeFactory->createFuncCall('round', [$expr, new LNumber(2), new ConstFetch(new Name('PHP_ROUND_HALF_ODD'))]);
        $funcCall->name = new Name('number_format');
        $funcCall->args = [new Arg($roundFuncCall), new Arg(new LNumber(2)), new Arg(new String_('.')), new Arg(new String_(''))];
        return $funcCall;
    }
}
