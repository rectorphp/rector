<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IntegerType;
use Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer;
use Rector\DowngradePhp81\NodeFactory\ArrayMergeFromArraySpreadFactory;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/array_unpacking_string_keys
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\DowngradeArraySpreadStringKeyRectorTest
 */
final class DowngradeArraySpreadStringKeyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArrayMergeFromArraySpreadFactory $arrayMergeFromArraySpreadFactory;
    /**
     * @readonly
     */
    private ArraySpreadAnalyzer $arraySpreadAnalyzer;
    public function __construct(ArrayMergeFromArraySpreadFactory $arrayMergeFromArraySpreadFactory, ArraySpreadAnalyzer $arraySpreadAnalyzer)
    {
        $this->arrayMergeFromArraySpreadFactory = $arrayMergeFromArraySpreadFactory;
        $this->arraySpreadAnalyzer = $arraySpreadAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace array spread with string key to array_merge function', [new CodeSample(<<<'CODE_SAMPLE'
$parts = ['a' => 'b'];
$parts2 = ['c' => 'd'];

$result = [...$parts, ...$parts2];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$parts = ['a' => 'b'];
$parts2 = ['c' => 'd'];

$result = array_merge($parts, $parts2);
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
    public function refactor(Node $node) : ?Node
    {
        if (!$this->arraySpreadAnalyzer->isArrayWithUnpack($node)) {
            return null;
        }
        if ($this->shouldSkipArray($node)) {
            return null;
        }
        /** @var MutatingScope $scope */
        $scope = ScopeFetcher::fetch($node);
        return $this->arrayMergeFromArraySpreadFactory->createFromArray($node, $scope);
    }
    private function shouldSkipArray(Array_ $array) : bool
    {
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            $type = $this->nodeTypeResolver->getType($item->value);
            if (!$type instanceof ArrayType && !$type instanceof ConstantArrayType) {
                continue;
            }
            $keyType = $type->getKeyType();
            if ($keyType instanceof IntegerType) {
                return \true;
            }
        }
        return \false;
    }
}
