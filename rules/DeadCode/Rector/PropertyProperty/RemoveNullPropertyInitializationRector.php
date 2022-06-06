<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\PropertyProperty;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PhpParser\Node\Stmt\PropertyProperty;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use function strtolower;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\RemoveNullPropertyInitializationRectorTest
 */
final class RemoveNullPropertyInitializationRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove initialization with null value from property declarations', [new CodeSample(<<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar = null;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [PropertyProperty::class];
    }
    /**
     * @param PropertyProperty $node
     */
    public function refactor(Node $node) : ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        // skip typed properties
        if ($parent instanceof Property && $parent->type !== null) {
            return null;
        }
        $defaultValueNode = $node->default;
        if (!$defaultValueNode instanceof Expr) {
            return null;
        }
        if (!$defaultValueNode instanceof ConstFetch) {
            return null;
        }
        if (strtolower((string) $defaultValueNode->name) !== 'null') {
            return null;
        }
        $nodeNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if ($nodeNode instanceof NullableType) {
            return null;
        }
        $node->default = null;
        return $node;
    }
}
