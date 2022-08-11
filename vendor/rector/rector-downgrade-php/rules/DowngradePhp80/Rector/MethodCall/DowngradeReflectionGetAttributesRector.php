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
final class DowngradeReflectionGetAttributesRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove reflection getAttributes() class method code', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'getAttributes')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Reflector'))) {
            return null;
        }
        $args = [new Arg($node->var), new Arg(new String_('getAttributes'))];
        $ternary = new Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, new Array_([]));
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Ternary) {
            return $ternary;
        }
        if (!$this->nodeComparator->areNodesEqual($parentNode, $ternary)) {
            return $ternary;
        }
        return null;
    }
}
