<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\DowngradeReflectionGetTypeRectorTest
 */
final class DowngradeReflectionGetTypeRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade reflection $refleciton->getType() method call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty->getType()) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        if (null) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node->name, 'getType')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('ReflectionProperty'))) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Expr\Instanceof_) {
            return null;
        }
        $args = [new \PhpParser\Node\Arg($node->var), new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('getType'))];
        $ternary = new \PhpParser\Node\Expr\Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, $this->nodeFactory->createNull());
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\Ternary) {
            return $ternary;
        }
        if (!$this->nodeComparator->areNodesEqual($parent, $ternary)) {
            return $ternary;
        }
        return null;
    }
}
