<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use Rector\CodeQuality\NodeFactory\PropertyTypeDecorator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\DowngradeTypedPropertyRectorTest
 */
final class DowngradeTypedPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeFactory\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    public function __construct(\Rector\CodeQuality\NodeFactory\PropertyTypeDecorator $propertyTypeDecorator)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes property type definition from type definitions to `@var` annotations.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private string $property;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string
     */
    private $property;
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->type === null) {
            return null;
        }
        $default = $node->props[0]->default;
        if ($node->type instanceof \PhpParser\Node\NullableType && $default instanceof \PhpParser\Node\Expr && $this->valueResolver->isNull($default)) {
            $node->props[0]->default = null;
        }
        $this->propertyTypeDecorator->decoratePropertyWithDocBlock($node, $node->type);
        $node->type = null;
        return $node;
    }
}
