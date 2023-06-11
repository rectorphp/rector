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
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\Naming\VariableNaming;
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
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(ArgsAnalyzer $argsAnalyzer, VariableNaming $variableNaming)
    {
        $this->argsAnalyzer = $argsAnalyzer;
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
        return [Expression::class, Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     * @return Stmt[]|null
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?array
    {
        $funcCall = $this->matchFunCall($node);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($funcCall, 'money_format')) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        $args = $funcCall->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        $formatValue = $args[0]->value;
        if (!$this->valueResolver->isValue($formatValue, '%i')) {
            return null;
        }
        return $this->resolveNumberFormat($funcCall, $args[1]->value, $scope, $node);
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $stmt
     */
    private function resolveNumberFormat(FuncCall $funcCall, Expr $expr, Scope $scope, $stmt) : array
    {
        $newValue = $this->nodeFactory->createFuncCall('round', [$expr, new LNumber(2), new ConstFetch(new Name('PHP_ROUND_HALF_ODD'))]);
        $countedVariableName = $this->variableNaming->createCountedValueName('roundedValue', $scope);
        $variable = new Variable($countedVariableName);
        $funcCall->name = new Name('number_format');
        $funcCall->args = [new Arg($variable), new Arg(new LNumber(2)), new Arg(new String_('.')), new Arg(new String_(''))];
        return [new Expression(new Assign($variable, $newValue)), $stmt];
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     */
    private function matchFunCall($node) : ?\PhpParser\Node\Expr\FuncCall
    {
        if ($node->expr instanceof Assign) {
            $assign = $node->expr;
            if ($assign->expr instanceof FuncCall) {
                return $assign->expr;
            }
            return null;
        }
        if ($node->expr instanceof FuncCall) {
            return $node->expr;
        }
        return null;
    }
}
