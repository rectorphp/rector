<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Type\Type;
use Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer;
use Rector\DowngradePhp81\NodeFactory\ArrayMergeFromArraySpreadFactory;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/spread_operator_for_array
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\DowngradeArraySpreadRectorTest
 */
final class DowngradeArraySpreadRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArrayMergeFromArraySpreadFactory $arrayMergeFromArraySpreadFactory;
    /**
     * @readonly
     */
    private ArraySpreadAnalyzer $arraySpreadAnalyzer;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(ArrayMergeFromArraySpreadFactory $arrayMergeFromArraySpreadFactory, ArraySpreadAnalyzer $arraySpreadAnalyzer, AstResolver $astResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->arrayMergeFromArraySpreadFactory = $arrayMergeFromArraySpreadFactory;
        $this->arraySpreadAnalyzer = $arraySpreadAnalyzer;
        $this->astResolver = $astResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace array spread with array_merge function', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = ['banana', 'orange', ...$parts, 'watermelon'];
    }

    public function runWithIterable()
    {
        $fruits = ['banana', 'orange', ...new ArrayIterator(['durian', 'kiwi']), 'watermelon'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon']);
    }

    public function runWithIterable()
    {
        $fruits = array_merge(
            ['banana', 'orange'],
            is_array(new ArrayIterator(['durian', 'kiwi'])) ?
                new ArrayIterator(['durian', 'kiwi']) :
                iterator_to_array(new ArrayIterator(['durian', 'kiwi'])),
            ['watermelon']
        );
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
        return [Array_::class, ClassConst::class];
    }
    /**
     * @param Array_|ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassConst) {
            return $this->refactorUnderClassConst($node);
        }
        if (!$this->arraySpreadAnalyzer->isArrayWithUnpack($node)) {
            return null;
        }
        /** @var MutatingScope $scope */
        $scope = ScopeFetcher::fetch($node);
        return $this->arrayMergeFromArraySpreadFactory->createFromArray($node, $scope);
    }
    private function refactorUnderClassConst(ClassConst $classConst) : ?ClassConst
    {
        $arrays = $this->betterNodeFinder->findInstanceOf($classConst->consts, Array_::class);
        if ($arrays === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($arrays as $array) {
            $refactorArrayConstValue = $this->refactorArrayConstValue($array);
            if ($refactorArrayConstValue instanceof Array_) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $classConst;
        }
        return null;
    }
    private function resolveItemType(?ArrayItem $arrayItem) : ?Type
    {
        if (!$arrayItem instanceof ArrayItem) {
            return null;
        }
        if (!$arrayItem->unpack) {
            return null;
        }
        if (!$arrayItem->value instanceof ClassConstFetch) {
            return null;
        }
        if (!$arrayItem->value->class instanceof Name) {
            return null;
        }
        if (!$arrayItem->value->name instanceof Identifier) {
            return null;
        }
        return $this->nodeTypeResolver->getType($arrayItem->value->class);
    }
    private function refactorArrayConstValue(Array_ $array) : ?Array_
    {
        $hasChanged = \false;
        foreach ($array->items as $key => $item) {
            $type = $this->resolveItemType($item);
            if (!$type instanceof FullyQualifiedObjectType) {
                continue;
            }
            $value = $item->value;
            /** @var ClassConstFetch $value */
            $name = $value->name;
            /** @var Identifier $name */
            $classLike = $this->astResolver->resolveClassFromName($type->getClassName());
            if (!$classLike instanceof ClassLike) {
                continue;
            }
            $constants = $classLike->getConstants();
            foreach ($constants as $constant) {
                $const = $constant->consts[0];
                if ($const->name->toString() === $name->toString() && $const->value instanceof Array_) {
                    unset($array->items[$key]);
                    \array_splice($array->items, $key, 0, $const->value->items);
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged) {
            return $array;
        }
        return null;
    }
}
