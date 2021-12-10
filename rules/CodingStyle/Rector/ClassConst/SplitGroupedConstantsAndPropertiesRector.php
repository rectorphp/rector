<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector\SplitGroupedConstantsAndPropertiesRectorTest
 */
final class SplitGroupedConstantsAndPropertiesRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Separate constant and properties to own lines', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true, AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt, $isIsThough;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true;
    const AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt;

    /**
     * @var string
     */
    public $isIsThough;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassConst::class, \PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param ClassConst|Property $node
     * @return Node[]|null
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassConst) {
            if (\count($node->consts) < 2) {
                return null;
            }
            /** @var Const_[] $allConsts */
            $allConsts = $node->consts;
            /** @var Const_ $firstConst */
            $firstConst = \array_shift($allConsts);
            $node->consts = [$firstConst];
            $nextClassConsts = $this->createNextClassConsts($allConsts, $node);
            return \array_merge([$node], $nextClassConsts);
        }
        if (\count($node->props) < 2) {
            return null;
        }
        $allProperties = $node->props;
        /** @var PropertyProperty $firstPropertyProperty */
        $firstPropertyProperty = \array_shift($allProperties);
        $node->props = [$firstPropertyProperty];
        $nextProperties = [];
        foreach ($allProperties as $allProperty) {
            $nextProperties[] = new \PhpParser\Node\Stmt\Property($node->flags, [$allProperty], $node->getAttributes());
        }
        $item0Unpacked = [$node];
        return \array_merge($item0Unpacked, $nextProperties);
    }
    /**
     * @param Const_[] $consts
     * @return ClassConst[]
     */
    private function createNextClassConsts(array $consts, \PhpParser\Node\Stmt\ClassConst $classConst) : array
    {
        $decoratedConsts = [];
        foreach ($consts as $const) {
            $decoratedConsts[] = new \PhpParser\Node\Stmt\ClassConst([$const], $classConst->flags, $classConst->getAttributes());
        }
        return $decoratedConsts;
    }
}
