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
final class ArraySpreadInsteadOfArrayMergeRector extends AbstractRector implements MinPhpVersionInterface
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
    public function __construct(ArrayTypeAnalyzer $arrayTypeAnalyzer, PhpVersionProvider $phpVersionProvider)
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array_merge() to spread operator', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isName($node, 'array_merge')) {
            return $this->refactorArray($node);
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_SPREAD;
    }
    private function refactorArray(FuncCall $funcCall) : ?Array_
    {
        $array = new Array_();
        foreach ($funcCall->args as $arg) {
            if (!$arg instanceof Arg) {
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
            if ($value instanceof Array_) {
                $array->items = \array_merge($array->items, $value->items);
                continue;
            }
            $value = $this->resolveValue($value);
            $array->items[] = $this->createUnpackedArrayItem($value);
        }
        return $array;
    }
    private function shouldSkipArrayForInvalidTypeOrKeys(Expr $expr) : bool
    {
        // we have no idea what it is → cannot change it
        if (!$this->arrayTypeAnalyzer->isArrayType($expr)) {
            return \true;
        }
        $arrayStaticType = $this->getType($expr);
        if (!$arrayStaticType instanceof ArrayType) {
            return \true;
        }
        return !$this->isArrayKeyTypeAllowed($arrayStaticType);
    }
    private function isArrayKeyTypeAllowed(ArrayType $arrayType) : bool
    {
        if ($arrayType->getKeyType()->isInteger()->yes()) {
            return \true;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::ARRAY_SPREAD_STRING_KEYS)) {
            return \false;
        }
        return $arrayType->getKeyType()->isString()->yes();
    }
    private function resolveValue(Expr $expr) : Expr
    {
        if ($expr instanceof FuncCall && $this->isIteratorToArrayFuncCall($expr)) {
            /** @var Arg $arg */
            $arg = $expr->args[0];
            /** @var FuncCall $expr */
            $expr = $arg->value;
        }
        if (!$expr instanceof Ternary) {
            return $expr;
        }
        if (!$expr->cond instanceof FuncCall) {
            return $expr;
        }
        if (!$this->isName($expr->cond, 'is_array')) {
            return $expr;
        }
        if ($expr->if instanceof Variable && $this->isIteratorToArrayFuncCall($expr->else)) {
            return $expr->if;
        }
        return $expr;
    }
    private function createUnpackedArrayItem(Expr $expr) : ArrayItem
    {
        return new ArrayItem($expr, null, \false, [], \true);
    }
    private function isIteratorToArrayFuncCall(Expr $expr) : bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr, 'iterator_to_array')) {
            return \false;
        }
        if ($expr->isFirstClassCallable()) {
            return \false;
        }
        return isset($expr->getArgs()[0]);
    }
}
