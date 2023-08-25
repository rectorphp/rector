<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeManipulator\PropertyDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\Property\DowngradeMixedTypeTypedPropertyRector\DowngradeMixedTypeTypedPropertyRectorTest
 */
final class DowngradeMixedTypeTypedPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\PropertyDecorator
     */
    private $PropertyDecorator;
    public function __construct(PropertyDecorator $PropertyDecorator)
    {
        $this->PropertyDecorator = $PropertyDecorator;
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
        return new RuleDefinition('Removes mixed type property type definition, adding `@var` annotations instead.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private mixed $property;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var mixed
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
        if (!$node->type instanceof Identifier) {
            return null;
        }
        if ($node->type->toString() !== 'mixed') {
            return null;
        }
        $this->PropertyDecorator->decorateWithDocBlock($node, $node->type);
        $node->type = null;
        return $node;
    }
}
