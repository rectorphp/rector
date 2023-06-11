<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\DowngradeReflectionGetTypeRectorTest
 */
final class DowngradeReflectionGetTypeRector extends AbstractRector
{
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
        return [MethodCall::class, Instanceof_::class, FuncCall::class];
    }
    /**
     * @param MethodCall|Instanceof_|FuncCall $node
     * @return \PhpParser\Node|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Instanceof_) {
            return $this->checkInstanceof($node);
        }
        if ($node instanceof FuncCall) {
            return $this->checkFuncCall($node);
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
    private function checkInstanceof(Instanceof_ $instanceof) : ?int
    {
        // skip instance of on call
        if ($instanceof->expr instanceof MethodCall) {
            return NodeTraverser::STOP_TRAVERSAL;
        }
        return null;
    }
    private function checkFuncCall(FuncCall $funcCall) : ?int
    {
        if ($this->isName($funcCall, 'method_exists')) {
            return NodeTraverser::STOP_TRAVERSAL;
        }
        return null;
    }
}
