<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.array-filter.php#refsect1-function.array-filter-changelog
 * @see https://3v4l.org/lqHFA
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\DowngradeArrayFilterNullableCallbackRectorTest
 */
final class DowngradeArrayFilterNullableCallbackRector extends AbstractRector
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Unset nullable callback on array_filter', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($callback = null)
    {
        $data = [[]];
        var_dump(array_filter($data, null));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($callback = null)
    {
        $data = [[]];
        var_dump(array_filter($data));
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\Ternary|null
     */
    public function refactor(Node $node)
    {
        if (!$this->isName($node, 'array_filter')) {
            return null;
        }
        $args = $node->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        if (!isset($args[1])) {
            return null;
        }
        // direct null check ConstFetch
        $secondArg = $args[1];
        if ($secondArg->value instanceof ConstFetch && $this->valueResolver->isNull($secondArg->value)) {
            $args = [$args[0]];
            $node->args = $args;
            return $node;
        }
        if ($this->shouldSkip($secondArg->value)) {
            return null;
        }
        $node->args[1] = new Arg($this->createNewArgFirstTernary($args));
        $node->args[2] = new Arg($this->createNewArgSecondTernary($args));
        return $node;
    }
    private function shouldSkip(Expr $expr) : bool
    {
        if ($this->isAlreadyConditionedToNull($expr)) {
            return \true;
        }
        if (\in_array(\get_class($expr), [String_::class, Closure::class, ArrowFunction::class, Array_::class], \true)) {
            return \true;
        }
        $type = $this->nodeTypeResolver->getType($expr);
        return \in_array(\get_class($type), [StringType::class, ConstantStringType::class, ArrayType::class, ClosureType::class], \true);
    }
    /**
     * @param Arg[] $args
     */
    private function createNewArgFirstTernary(array $args) : Ternary
    {
        $identical = new Identical($args[1]->value, $this->nodeFactory->createNull());
        $dummyVariable = new Variable('value');
        $booleanNot = new BooleanNot(new Empty_($dummyVariable));
        $arrowFunction = $this->createArrowFunction($booleanNot, $dummyVariable);
        return new Ternary($identical, $arrowFunction, $args[1]->value);
    }
    /**
     * @param Arg[] $args
     */
    private function createNewArgSecondTernary(array $args) : Ternary
    {
        $identical = new Identical($args[1]->value, $this->nodeFactory->createNull());
        $constFetch = new ConstFetch(new Name('ARRAY_FILTER_USE_BOTH'));
        return new Ternary($identical, $constFetch, isset($args[2]) ? $args[2]->value : new LNumber(0));
    }
    private function isAlreadyConditionedToNull(Expr $expr) : bool
    {
        if (!$expr instanceof Ternary) {
            return \false;
        }
        if (!$expr->cond instanceof Identical) {
            return \false;
        }
        $identical = $expr->cond;
        return $this->valueResolver->isNull($identical->right);
    }
    private function createArrowFunction(BooleanNot $booleanNot, Variable $dummyVariable) : ArrowFunction
    {
        $arrowFunction = new ArrowFunction(['expr' => $booleanNot]);
        $arrowFunction->params = [new Param($dummyVariable), new Param(new Variable('key'))];
        $arrowFunction->returnType = new Identifier('bool');
        return $arrowFunction;
    }
}
