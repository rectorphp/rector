<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\CodeQuality\NodeFactory\PropertyTypeDecorator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector\DowngradeUnionTypeTypedPropertyRectorTest
 */
final class DowngradeUnionTypeTypedPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeFactory\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    public function __construct(PropertyTypeDecorator $propertyTypeDecorator)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes union type property type definition, adding `@var` annotations instead.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private string|int $property;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string|int
     */
    private $property;
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->type === null) {
            return null;
        }
        if (!$this->shouldRemoveProperty($node)) {
            return null;
        }
        $this->propertyTypeDecorator->decoratePropertyWithDocBlock($node, $node->type);
        $node->type = null;
        return $node;
    }
    private function shouldRemoveProperty(Property $property) : bool
    {
        if ($property->type === null) {
            return \false;
        }
        // Check it is the union type
        return $property->type instanceof UnionType;
    }
}
