<?php

declare(strict_types=1);

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
 * @see \Rector\CodingStyle\Tests\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector\SplitGroupedConstantsAndPropertiesRectorTest
 */
final class SplitGroupedConstantsAndPropertiesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Separate constant and properties to own lines',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true, AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt, $isIsThough;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class, Property::class];
    }

    /**
     * @param ClassConst|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassConst) {
            if (count($node->consts) < 2) {
                return null;
            }

            /** @var Const_[] $allConsts */
            $allConsts = $node->consts;

            /** @var Const_ $firstConst */
            $firstConst = array_shift($allConsts);
            $node->consts = [$firstConst];

            $nextClassConsts = $this->createNextClassConsts($allConsts, $node);
            $this->addNodesAfterNode($nextClassConsts, $node);

            return $node;
        }

        if (count($node->props) < 2) {
            return null;
        }

        $allProperties = $node->props;
        /** @var PropertyProperty $firstPropertyProperty */
        $firstPropertyProperty = array_shift($allProperties);
        $node->props = [$firstPropertyProperty];

        foreach ($allProperties as $anotherProperty) {
            $nextProperty = new Property($node->flags, [$anotherProperty], $node->getAttributes());
            $this->addNodeAfterNode($nextProperty, $node);
        }

        return $node;
    }

    /**
     * @param Const_[] $consts
     * @return ClassConst[]
     */
    private function createNextClassConsts(array $consts, ClassConst $classConst): array
    {
        $decoratedConsts = [];

        foreach ($consts as $const) {
            $decoratedConsts[] = new ClassConst([$const], $classConst->flags, $classConst->getAttributes());
        }

        return $decoratedConsts;
    }
}
