<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp81\Rector\MethodCall\DowngradeIsEnumRector\DowngradeIsEnumRectorTest
 */
final class DowngradeIsEnumRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrades isEnum() on class reflection', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionClass $reflectionClass)
    {
        return $reflectionClass->isEnum();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionClass $reflectionClass)
    {
        return method_exists($reflectionClass, 'isEnum') ? $reflectionClass->isEnum() : false;
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
        if (!$this->isName($node->name, 'isEnum')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('ReflectionClass'))) {
            return null;
        }
        $args = [new Arg($node->var), new Arg(new String_('isEnum'))];
        return new Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, $this->nodeFactory->createFalse());
    }
}
