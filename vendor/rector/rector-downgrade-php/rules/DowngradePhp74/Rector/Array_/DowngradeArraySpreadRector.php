<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer;
use Rector\DowngradePhp81\NodeFactory\ArrayMergeFromArraySpreadFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/spread_operator_for_array
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\DowngradeArraySpreadRectorTest
 */
final class DowngradeArraySpreadRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp81\NodeFactory\ArrayMergeFromArraySpreadFactory
     */
    private $arrayMergeFromArraySpreadFactory;
    /**
     * @readonly
     * @var \Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer
     */
    private $arraySpreadAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(ArrayMergeFromArraySpreadFactory $arrayMergeFromArraySpreadFactory, ArraySpreadAnalyzer $arraySpreadAnalyzer, AstResolver $astResolver)
    {
        $this->arrayMergeFromArraySpreadFactory = $arrayMergeFromArraySpreadFactory;
        $this->arraySpreadAnalyzer = $arraySpreadAnalyzer;
        $this->astResolver = $astResolver;
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
        $item0Unpacked = new ArrayIterator(['durian', 'kiwi']);
        $fruits = array_merge(['banana', 'orange'], is_array($item0Unpacked) ? $item0Unpacked : iterator_to_array($item0Unpacked), ['watermelon']);
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
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($node instanceof ClassConst) {
            return $this->refactorUnderClassConst($node);
        }
        if (!$this->arraySpreadAnalyzer->isArrayWithUnpack($node)) {
            return null;
        }
        $shouldIncrement = (bool) $this->betterNodeFinder->findFirstNext($node, function (Node $subNode) : bool {
            if (!$subNode instanceof Array_) {
                return \false;
            }
            return $this->arraySpreadAnalyzer->isArrayWithUnpack($subNode);
        });
        /** @var MutatingScope $scope */
        return $this->arrayMergeFromArraySpreadFactory->createFromArray($node, $scope, $this->file, $shouldIncrement);
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
            /**
             * @var ArrayItem $item
             * @var ClassConstFetch $value
             * @var Identifier $name
             */
            $value = $item->value;
            /** @var Identifier $name */
            $name = $value->name;
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
