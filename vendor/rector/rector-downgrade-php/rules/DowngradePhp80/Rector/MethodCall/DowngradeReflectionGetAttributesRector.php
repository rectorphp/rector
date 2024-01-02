<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\DowngradeReflectionGetAttributesRectorTest
 */
final class DowngradeReflectionGetAttributesRector extends AbstractRector
{
    /**
     * @var string
     */
    private const IS_IF_TERNARY = 'is_if_ternary';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove reflection getAttributes() class method code', [new CodeSample(<<<'CODE_SAMPLE'
function run(ReflectionClass $reflectionClass)
{
    return $reflectionClass->getAttributes();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run(ReflectionClass $reflectionClass)
{
    return method_exists($reflectionClass, 'getAttributes') ? $reflectionClass->getAttributes() ? [];
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class, MethodCall::class];
    }
    /**
     * @param Ternary|MethodCall $node
     * @return \PhpParser\Node\Expr\Ternary|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Ternary) {
            if ($node->if instanceof Expr && $node->cond instanceof FuncCall && $this->isName($node->cond, 'method_exists')) {
                $node->if->setAttribute(self::IS_IF_TERNARY, \true);
            }
            return null;
        }
        if (!$this->isName($node->name, 'getAttributes')) {
            return null;
        }
        if ($node->getAttribute(self::IS_IF_TERNARY) === \true) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Reflector'))) {
            return null;
        }
        $args = [new Arg($node->var), new Arg(new String_('getAttributes'))];
        $methodExistsFuncCall = $this->nodeFactory->createFuncCall('method_exists', $args);
        return new Ternary($methodExistsFuncCall, $node, new Array_([]));
    }
}
