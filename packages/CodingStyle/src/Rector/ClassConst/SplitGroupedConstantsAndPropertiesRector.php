<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SplitGroupedConstantsAndPropertiesRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Separate constant and properties to own lines', [
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

            $allConstants = $node->consts;

            /** @var Node\Const_ $firstConstant */
            $firstConstant = array_shift($allConstants);
            $node->consts = [$firstConstant];

            foreach ($allConstants as $anotherConstant) {
                $nextClassConst = new ClassConst([$anotherConstant], $node->flags, $node->getAttributes());
                $this->addNodeAfterNode($nextClassConst, $node);
            }

            return $node;
        }

        if (count($node->props) < 2) {
            return null;
        }

        $allProperties = $node->props;
        /** @var Node\Stmt\PropertyProperty $firstProperty */
        $firstProperty = array_shift($allProperties);
        $node->props = [$firstProperty];

        foreach ($allProperties as $anotherProperty) {
            $nextProperty = new Property($node->flags, [$anotherProperty], $node->getAttributes());
            $this->addNodeAfterNode($nextProperty, $node);
        }

        return $node;
    }
}
