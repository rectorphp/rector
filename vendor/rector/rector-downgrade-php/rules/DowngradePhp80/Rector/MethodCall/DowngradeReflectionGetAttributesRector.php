<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
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
        return [MethodCall::class, FuncCall::class];
    }
    /**
     * @param MethodCall|Node\Expr\FuncCall $node
     * @return \PhpParser\Node\Expr\Ternary|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof FuncCall) {
            if ($this->isName($node, 'method_exists')) {
                return NodeTraverser::STOP_TRAVERSAL;
            }
            return null;
        }
        if (!$this->isName($node->name, 'getAttributes')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Reflector'))) {
            return null;
        }
        $args = [new Arg($node->var), new Arg(new String_('getAttributes'))];
        return new Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, new Array_([]));
    }
}
