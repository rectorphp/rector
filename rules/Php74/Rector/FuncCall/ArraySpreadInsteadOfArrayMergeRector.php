<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/spread_operator_for_array
 *
 * @see \Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Php74ArraySpreadInsteadOfArrayMergeRectorTest
 * @see \Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Php81ArraySpreadInsteadOfArrayMergeRectorTest
 */
final class ArraySpreadInsteadOfArrayMergeRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer $arrayTypeAnalyzer, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change array_merge() to spread operator', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($iter1, $iter2)
    {
        $values = array_merge(iterator_to_array($iter1), iterator_to_array($iter2));

        // Or to generalize to all iterables
        $anotherValues = array_merge(
            is_array($iter1) ? $iter1 : iterator_to_array($iter1),
            is_array($iter2) ? $iter2 : iterator_to_array($iter2)
        );
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($iter1, $iter2)
    {
        $values = [...$iter1, ...$iter2];

        // Or to generalize to all iterables
        $anotherValues = [...$iter1, ...$iter2];
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
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isName($node, 'array_merge')) {
            return $this->refactorArray($node);
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ARRAY_SPREAD;
    }
    private function refactorArray(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\Array_
    {
        $array = new \PhpParser\Node\Expr\Array_();
        foreach ($funcCall->args as $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            // cannot handle unpacked arguments
            if ($arg->unpack) {
                return null;
            }
            $value = $arg->value;
            if ($this->shouldSkipArrayForInvalidTypeOrKeys($value)) {
                return null;
            }
            $value = $this->resolveValue($value);
            $array->items[] = $this->createUnpackedArrayItem($value);
        }
        return $array;
    }
    private function shouldSkipArrayForInvalidTypeOrKeys(\PhpParser\Node\Expr $expr) : bool
    {
        // we have no idea what it is → cannot change it
        if (!$this->arrayTypeAnalyzer->isArrayType($expr)) {
            return \true;
        }
        $arrayStaticType = $this->getType($expr);
        if (!$arrayStaticType instanceof \PHPStan\Type\ArrayType) {
            return \true;
        }
        return !$this->isArrayKeyTypeAllowed($arrayStaticType);
    }
    private function isArrayKeyTypeAllowed(\PHPStan\Type\ArrayType $arrayType) : bool
    {
        $allowedKeyTypes = [\PHPStan\Type\IntegerType::class];
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::ARRAY_SPREAD_STRING_KEYS)) {
            $allowedKeyTypes[] = \PHPStan\Type\StringType::class;
        }
        foreach ($allowedKeyTypes as $allowedKeyType) {
            if ($arrayType->getKeyType() instanceof $allowedKeyType) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveValue(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr
    {
        if ($expr instanceof \PhpParser\Node\Expr\FuncCall && $this->isIteratorToArrayFuncCall($expr)) {
            /** @var Arg $arg */
            $arg = $expr->args[0];
            /** @var FuncCall $expr */
            $expr = $arg->value;
        }
        if (!$expr instanceof \PhpParser\Node\Expr\Ternary) {
            return $expr;
        }
        if (!$expr->cond instanceof \PhpParser\Node\Expr\FuncCall) {
            return $expr;
        }
        if (!$this->isName($expr->cond, 'is_array')) {
            return $expr;
        }
        if ($expr->if instanceof \PhpParser\Node\Expr\Variable && $this->isIteratorToArrayFuncCall($expr->else)) {
            return $expr->if;
        }
        return $expr;
    }
    private function createUnpackedArrayItem(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\ArrayItem
    {
        return new \PhpParser\Node\Expr\ArrayItem($expr, null, \false, [], \true);
    }
    private function isIteratorToArrayFuncCall(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr, 'iterator_to_array')) {
            return \false;
        }
        if (!isset($expr->args[0])) {
            return \false;
        }
        return $expr->args[0] instanceof \PhpParser\Node\Arg;
    }
}
