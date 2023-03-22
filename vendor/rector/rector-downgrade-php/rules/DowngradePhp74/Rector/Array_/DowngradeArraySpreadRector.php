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
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer;
use Rector\DowngradePhp81\NodeFactory\ArrayMergeFromArraySpreadFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        return [Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$this->arraySpreadAnalyzer->isArrayWithUnpack($node)) {
            return null;
        }
        $classConst = $this->betterNodeFinder->findParentType($node, ClassConst::class);
        if ($classConst instanceof ClassConst) {
            $refactorUnderClassConst = $this->refactorUnderClassConst($node, $classConst);
            if ($refactorUnderClassConst instanceof Array_) {
                return $refactorUnderClassConst;
            }
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
    private function refactorUnderClassConst(Array_ $array, ClassConst $classConst) : ?Array_
    {
        $node = $classConst->getAttribute(AttributeKey::PARENT_NODE);
        $hasChanged = \false;
        if (!$node instanceof ClassLike) {
            return null;
        }
        foreach ($array->items as $key => $item) {
            if ($item instanceof ArrayItem && $item->unpack && $item->value instanceof ClassConstFetch && $item->value->class instanceof Name) {
                $type = $this->nodeTypeResolver->getType($item->value->class);
                $name = $item->value->name;
                if ($type instanceof FullyQualifiedObjectType && $name instanceof Identifier) {
                    $classLike = $this->astResolver->resolveClassFromName($type->getClassName());
                    if (!$classLike instanceof ClassLike) {
                        continue;
                    }
                    $constants = $classLike->getConstants();
                    foreach ($constants as $constant) {
                        $const = $constant->consts[0];
                        if ($const->name instanceof Identifier && $const->name->toString() === $name->toString() && $const->value instanceof Array_) {
                            unset($array->items[$key]);
                            \array_splice($array->items, $key, 0, $const->value->items);
                            $hasChanged = \true;
                        }
                    }
                }
            }
        }
        if ($hasChanged) {
            return $array;
        }
        return null;
    }
}
