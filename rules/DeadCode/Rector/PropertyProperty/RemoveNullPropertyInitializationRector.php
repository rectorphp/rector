<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\PropertyProperty;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function strtolower;
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
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->type instanceof Node) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->props as $prop) {
            $defaultValueNode = $prop->default;
            if (!$defaultValueNode instanceof Expr) {
                continue;
            }
            if (!$defaultValueNode instanceof ConstFetch) {
                continue;
            }
            if (strtolower((string) $defaultValueNode->name) !== 'null') {
                continue;
            }
            $prop->default = null;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
