<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Property\SplitGroupedPropertiesRector\SplitGroupedPropertiesRectorTest
 */
final class SplitGroupedPropertiesRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Separate grouped properties to own lines', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string
     */
    public $isIt, $isIsThough;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
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
        return [Property::class];
    }
    /**
     * @param Property $node
     * @return Property[]|null
     */
    public function refactor(Node $node) : ?array
    {
        $allProperties = $node->props;
        if (\count($allProperties) === 1) {
            return null;
        }
        /** @var PropertyProperty $firstPropertyProperty */
        $firstPropertyProperty = \array_shift($allProperties);
        $node->props = [$firstPropertyProperty];
        $nextProperties = [];
        foreach ($allProperties as $allProperty) {
            $nextProperties[] = new Property($node->flags, [$allProperty], $node->getAttributes());
        }
        return \array_merge([$node], \is_array($nextProperties) ? $nextProperties : \iterator_to_array($nextProperties));
    }
}
