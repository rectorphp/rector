<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer;
use Rector\DowngradePhp81\NodeFactory\ArrayMergeFromArraySpreadFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
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
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ArrayMergeFromArraySpreadFactory $arrayMergeFromArraySpreadFactory, ArraySpreadAnalyzer $arraySpreadAnalyzer, BetterNodeFinder $betterNodeFinder, ReflectionProvider $reflectionProvider)
    {
        $this->arrayMergeFromArraySpreadFactory = $arrayMergeFromArraySpreadFactory;
        $this->arraySpreadAnalyzer = $arraySpreadAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [Array_::class, ClassLike::class];
    }
    /**
     * @param Array_|ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassLike) {
            return $this->refactorUnderClassLike($node);
        }
        if (!$this->arraySpreadAnalyzer->isArrayWithUnpack($node)) {
            return null;
        }
        /** @var MutatingScope $scope */
        $scope = ScopeFetcher::fetch($node);
        return $this->arrayMergeFromArraySpreadFactory->createFromArray($node, $scope);
    }
    private function refactorUnderClassLike(ClassLike $classLike): ?ClassLike
    {
        $hasChanged = \false;
        foreach ($classLike->getConstants() as $classConst) {
            if ($this->refactorUnderClassConst($classConst, $classLike)) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $classLike;
        }
        return null;
    }
    private function refactorUnderClassConst(ClassConst $classConst, ClassLike $classLike): bool
    {
        $arrays = $this->betterNodeFinder->findInstanceOf($classConst->consts, Array_::class);
        if ($arrays === []) {
            return \false;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($classConst->consts, function (Node $subNode) use (&$hasChanged, $classLike): ?Node {
            if (!$subNode instanceof Array_) {
                return null;
            }
            $refactorArrayConstValue = $this->refactorArrayConstValue($subNode, $classLike);
            if ($refactorArrayConstValue instanceof Array_) {
                $hasChanged = \true;
                return $refactorArrayConstValue;
            }
            return null;
        });
        return $hasChanged;
    }
    private function resolveItemType(?ArrayItem $arrayItem): ?Type
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
    private function refactorArrayConstValue(Array_ $array, ClassLike $classLike): ?Array_
    {
        $hasChanged = \false;
        $newArray = clone $array;
        $newArray->items = [];
        foreach ($array->items as $item) {
            $newArray->items[] = $item;
            $type = $this->resolveItemType($item);
            if (!$type instanceof FullyQualifiedObjectType) {
                continue;
            }
            $value = $item->value;
            /** @var ClassConstFetch $value */
            $name = $value->name;
            /** @var Identifier $name */
            $constantValueExpr = $this->resolveConstantValueExpr($classLike, $type->getClassName(), $name->toString());
            if (!$constantValueExpr instanceof Array_) {
                continue;
            }
            array_pop($newArray->items);
            $newArray->items = array_merge($newArray->items, $constantValueExpr->items);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $newArray;
        }
        return null;
    }
    private function resolveConstantValueExpr(ClassLike $classLike, string $className, string $constantName): ?Expr
    {
        // own class constant is read from current AST, to keep the names as written
        if ($this->isName($classLike, $className)) {
            foreach ($classLike->getConstants() as $classConst) {
                foreach ($classConst->consts as $const) {
                    if ($this->isName($const, $constantName)) {
                        return $const->value;
                    }
                }
            }
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->hasConstant($constantName)) {
            return null;
        }
        $valueExpr = $classReflection->getConstant($constantName)->getValueExpr();
        $contextDependentNode = $this->betterNodeFinder->findFirst($valueExpr, fn(Node $node): bool => $this->isContextDependentNode($node));
        return $contextDependentNode instanceof Node ? null : $valueExpr;
    }
    /**
     * These change meaning, or are no longer accessible, once moved to another class
     */
    private function isContextDependentNode(Node $node): bool
    {
        if ($node instanceof MagicConst) {
            return \true;
        }
        if ($node instanceof Name && $node->isSpecialClassName()) {
            return \true;
        }
        if ($node instanceof ConstFetch) {
            return $this->isNamespacedConstFetch($node);
        }
        return $node instanceof ClassConstFetch && $this->isNonPublicClassConstFetch($node);
    }
    private function isNamespacedConstFetch(ConstFetch $constFetch): bool
    {
        $namespacedName = $constFetch->name->getAttribute('namespacedName');
        if (!$namespacedName instanceof FullyQualified) {
            return \false;
        }
        return $this->reflectionProvider->hasConstant($namespacedName, null);
    }
    private function isNonPublicClassConstFetch(ClassConstFetch $classConstFetch): bool
    {
        if (!$classConstFetch->class instanceof Name || !$classConstFetch->name instanceof Identifier) {
            return \false;
        }
        $className = $classConstFetch->class->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $constantName = $classConstFetch->name->toString();
        if (!$classReflection->hasConstant($constantName)) {
            return \false;
        }
        return !$classReflection->getConstant($constantName)->isPublic();
    }
}
