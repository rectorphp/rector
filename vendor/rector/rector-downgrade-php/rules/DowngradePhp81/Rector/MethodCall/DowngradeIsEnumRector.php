<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp81\Rector\MethodCall\DowngradeIsEnumRector\DowngradeIsEnumRectorTest
 */
final class DowngradeIsEnumRector extends AbstractRector
{
    /**
     * @var string
     */
    private const IS_IF_TERNARY = 'is_if_ternary';
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [Ternary::class, MethodCall::class];
    }
    /**
     * @param Ternary|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Ternary) {
            if ($node->if instanceof Expr && $node->cond instanceof FuncCall && $this->isName($node->cond, 'method_exists')) {
                $node->if->setAttribute(self::IS_IF_TERNARY, \true);
            }
            return null;
        }
        if (!$this->isName($node->name, 'isEnum')) {
            return null;
        }
        // already wrapped by this rule on a previous pass
        if ($node->getAttribute(self::IS_IF_TERNARY) === \true) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('ReflectionClass'))) {
            return null;
        }
        $args = [new Arg($node->var), new Arg(new String_('isEnum'))];
        return new Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, $this->nodeFactory->createFalse());
    }
}
