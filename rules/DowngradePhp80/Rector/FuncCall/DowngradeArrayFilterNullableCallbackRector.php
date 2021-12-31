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
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.array-filter.php#refsect1-function.array-filter-changelog
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\DowngradeArrayFilterNullableCallbackRectorTest
 */
final class DowngradeArrayFilterNullableCallbackRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Unset nullable callback on array_filter', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\Ternary|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $args = $node->getArgs();
        if (!$this->isName($node, 'array_filter')) {
            return null;
        }
        if ($this->hasNamedArg($args)) {
            return null;
        }
        if (!isset($args[1])) {
            return null;
        }
        $createdByRule = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE) ?? [];
        if (\in_array(self::class, $createdByRule, \true)) {
            return null;
        }
        // direct null check ConstFetch
        if ($args[1]->value instanceof \PhpParser\Node\Expr\ConstFetch && $this->valueResolver->isNull($args[1]->value)) {
            $args = [$args[0]];
            $node->args = $args;
            return $node;
        }
        if ($this->shouldSkipSecondArg($args[1]->value)) {
            return null;
        }
        $node->args[1] = new \PhpParser\Node\Arg($this->createNewArgFirstTernary($args));
        $node->args[2] = new \PhpParser\Node\Arg($this->createNewArgSecondTernary($args));
        return $node;
    }
    private function shouldSkipSecondArg(\PhpParser\Node\Expr $expr) : bool
    {
        if (\in_array(\get_class($expr), [\PhpParser\Node\Scalar\String_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\ArrowFunction::class, \PhpParser\Node\Expr\Array_::class], \true)) {
            return \true;
        }
        $type = $this->nodeTypeResolver->getType($expr);
        return \in_array(\get_class($type), [\PHPStan\Type\StringType::class, \PHPStan\Type\Constant\ConstantStringType::class, \PHPStan\Type\ArrayType::class, \PHPStan\Type\ClosureType::class], \true);
    }
    /**
     * @param Arg[] $args
     */
    private function createNewArgFirstTernary(array $args) : \PhpParser\Node\Expr\Ternary
    {
        $identical = new \PhpParser\Node\Expr\BinaryOp\Identical($args[1]->value, $this->nodeFactory->createNull());
        $vVariable = new \PhpParser\Node\Expr\Variable('v');
        $arrowFunction = new \PhpParser\Node\Expr\ArrowFunction(['expr' => new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Empty_($vVariable))]);
        $arrowFunction->params = [new \PhpParser\Node\Param($vVariable), new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable('k'))];
        $arrowFunction->returnType = new \PhpParser\Node\Identifier('bool');
        return new \PhpParser\Node\Expr\Ternary($identical, $arrowFunction, $args[1]->value);
    }
    /**
     * @param Arg[] $args
     */
    private function createNewArgSecondTernary(array $args) : \PhpParser\Node\Expr\Ternary
    {
        $identical = new \PhpParser\Node\Expr\BinaryOp\Identical($args[1]->value, $this->nodeFactory->createNull());
        $constFetch = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('ARRAY_FILTER_USE_BOTH'));
        return new \PhpParser\Node\Expr\Ternary($identical, $constFetch, isset($args[2]) ? $args[2]->value : new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('0')));
    }
    /**
     * @param Arg[] $args
     */
    private function hasNamedArg(array $args) : bool
    {
        foreach ($args as $arg) {
            if ($arg->name instanceof \PhpParser\Node\Identifier) {
                return \true;
            }
        }
        return \false;
    }
}
