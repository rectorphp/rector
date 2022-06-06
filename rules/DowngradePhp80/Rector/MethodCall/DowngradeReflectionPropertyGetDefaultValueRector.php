<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\DowngradeReflectionPropertyGetDefaultValueRectorTest
 */
final class DowngradeReflectionPropertyGetDefaultValueRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade ReflectionProperty->getDefaultValue()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        return $reflectionProperty->getDefaultValue();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        return $reflectionProperty->getDeclaringClass()->getDefaultProperties()[$reflectionProperty->getName()] ?? null;
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
        if (!$this->nodeNameResolver->isName($node->name, 'getDefaultValue')) {
            return null;
        }
        $objectType = $this->nodeTypeResolver->getType($node->var);
        if (!$objectType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        if ($objectType->getClassName() !== 'ReflectionProperty') {
            return null;
        }
        $getName = new \PhpParser\Node\Expr\MethodCall($node->var, 'getName');
        $node = new \PhpParser\Node\Expr\MethodCall($node->var, 'getDeclaringClass');
        $node = new \PhpParser\Node\Expr\MethodCall($node, 'getDefaultProperties');
        $node = new \PhpParser\Node\Expr\ArrayDimFetch($node, $getName);
        return new \PhpParser\Node\Expr\BinaryOp\Coalesce($node, $this->nodeFactory->createNull());
    }
}
