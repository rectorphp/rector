<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\DowngradeReflectionGetTypeRectorTest
 */
final class DowngradeReflectionGetTypeRector extends AbstractRector
{
    /**
     * @var string
     */
    private const SKIP_NODE = 'skip_node';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade reflection $reflection->getType() method call', [new CodeSample(<<<'CODE_SAMPLE'
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
        if (method_exists($reflectionProperty, 'getType') ? $reflectionProperty->getType() ? null) {
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
        return [MethodCall::class, Ternary::class, Instanceof_::class];
    }
    /**
     * @param MethodCall|Ternary|Instanceof_ $node
     * @return \PhpParser\Node|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Instanceof_) {
            return $this->refactorInstanceof($node);
        }
        if ($node instanceof Ternary) {
            return $this->refactorTernary($node);
        }
        if ($node->getAttribute(self::SKIP_NODE) === \true) {
            return null;
        }
        if (!$this->isName($node->name, 'getType')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('ReflectionProperty'))) {
            return null;
        }
        $args = [new Arg($node->var), new Arg(new String_('getType'))];
        return new Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, $this->nodeFactory->createNull());
    }
    private function refactorInstanceof(Instanceof_ $instanceof) : ?Instanceof_
    {
        if (!$this->isName($instanceof->class, 'ReflectionNamedType')) {
            return null;
        }
        if (!$instanceof->expr instanceof MethodCall) {
            return null;
        }
        // checked typed → safe
        $instanceof->expr->setAttribute(self::SKIP_NODE, \true);
        return $instanceof;
    }
    private function refactorTernary(Ternary $ternary) : ?Ternary
    {
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if (!$ternary->cond instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($ternary->cond, 'method_exists')) {
            return null;
        }
        $ternary->if->setAttribute(self::SKIP_NODE, \true);
        return $ternary;
    }
}
