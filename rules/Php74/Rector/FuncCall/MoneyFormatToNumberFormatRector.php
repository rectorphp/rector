<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\Naming\VariableNaming;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.money-format.php#warning
 *
 * @see \Rector\Tests\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector\MoneyFormatToNumberFormatRectorTest
 */
final class MoneyFormatToNumberFormatRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const FORMATS = ['%i'];
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(ArgsAnalyzer $argsAnalyzer, NodesToAddCollector $nodesToAddCollector, VariableNaming $variableNaming)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->variableNaming = $variableNaming;
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
$roundedValue = round($value, 2, PHP_ROUND_HALF_ODD);
$value = number_format($roundedValue, 2, '.', '');
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
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$this->isName($node, 'money_format')) {
            return null;
        }
        $args = $node->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        $formatValue = $args[0]->value;
        foreach (self::FORMATS as $format) {
            if ($this->valueResolver->isValue($formatValue, $format)) {
                return $this->resolveNumberFormat($node, $args[1]->value, $scope);
            }
        }
        return null;
    }
    private function resolveNumberFormat(FuncCall $funcCall, Expr $expr, Scope $scope) : ?FuncCall
    {
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($funcCall);
        if (!$currentStmt instanceof Stmt) {
            return null;
        }
        $newValue = $this->nodeFactory->createFuncCall('round', [$expr, new LNumber(2), new ConstFetch(new Name('PHP_ROUND_HALF_ODD'))]);
        $variable = new Variable($this->variableNaming->createCountedValueName('roundedValue', $scope));
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression(new Assign($variable, $newValue)), $currentStmt);
        $funcCall->name = new Name('number_format');
        $funcCall->args[0] = new Arg($variable);
        $funcCall->args[1] = new Arg(new LNumber(2));
        $funcCall->args[2] = new Arg(new String_('.'));
        $funcCall->args[3] = new Arg(new String_(''));
        return $funcCall;
    }
}
