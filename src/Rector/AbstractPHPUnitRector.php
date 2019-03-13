<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\Node\Attribute;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    protected function isAssertMethod(Node $node, string $name): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return false;
        }

        return $this->isName($node, $name);
    }

    protected function isInTestClass(Node $node): bool
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        return $this->isTypes($classNode, ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase']);
    }
}
