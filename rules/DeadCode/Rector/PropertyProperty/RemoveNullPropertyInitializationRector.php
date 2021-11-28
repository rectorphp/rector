<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\PropertyProperty;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use function strtolower;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\RemoveNullPropertyInitializationRectorTest
 */
final class RemoveNullPropertyInitializationRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove initialization with null value from property declarations', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\PropertyProperty::class];
    }
    /**
     * @param PropertyProperty $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // skip typed properties
        if ($parent instanceof \PhpParser\Node\Stmt\Property && $parent->type !== null) {
            return null;
        }
        $defaultValueNode = $node->default;
        if (!$defaultValueNode instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$defaultValueNode instanceof \PhpParser\Node\Expr\ConstFetch) {
            return null;
        }
        if (\strtolower((string) $defaultValueNode->name) !== 'null') {
            return null;
        }
        $nodeNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if ($nodeNode instanceof \PhpParser\Node\NullableType) {
            return null;
        }
        $node->default = null;
        return $node;
    }
}
