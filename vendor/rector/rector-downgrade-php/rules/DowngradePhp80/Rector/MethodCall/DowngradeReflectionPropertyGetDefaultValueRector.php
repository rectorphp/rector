<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\DowngradeReflectionPropertyGetDefaultValueRectorTest
 */
final class DowngradeReflectionPropertyGetDefaultValueRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade ReflectionProperty->getDefaultValue()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeNameResolver->isName($node->name, 'getDefaultValue')) {
            return null;
        }
        $objectType = $this->nodeTypeResolver->getType($node->var);
        if (!$objectType instanceof ObjectType) {
            return null;
        }
        if ($objectType->getClassName() !== 'ReflectionProperty') {
            return null;
        }
        $getName = new MethodCall($node->var, 'getName');
        $getDeclaringClassMethodCall = new MethodCall($node->var, 'getDeclaringClass');
        $getDefaultPropertiesMethodCall = new MethodCall($getDeclaringClassMethodCall, 'getDefaultProperties');
        $arrayDimFetch = new ArrayDimFetch($getDefaultPropertiesMethodCall, $getName);
        return new Coalesce($arrayDimFetch, $this->nodeFactory->createNull());
    }
}
