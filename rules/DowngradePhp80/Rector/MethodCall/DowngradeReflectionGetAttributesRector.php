<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\DowngradeReflectionGetAttributesRectorTest
 */
final class DowngradeReflectionGetAttributesRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove reflection getAttributes() class method code', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->getAttributes()) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionClass $reflectionClass)
    {
        if ([]) {
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
        if (!$this->isName($node->name, 'getAttributes')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Reflector'))) {
            return null;
        }
        $args = [new \PhpParser\Node\Arg($node->var), new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('getAttributes'))];
        $ternary = new \PhpParser\Node\Expr\Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, new \PhpParser\Node\Expr\Array_([]));
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
