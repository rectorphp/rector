<?php

declare(strict_types=1);

namespace Rector\Tests\NodeCollector;

use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\Variable;
use PHPUnit\Framework\TestCase;
use Rector\NodeCollector\BinaryOpTreeRootLocator;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class BinaryOpTreeRootLocatorTest extends TestCase
{
    public function testLeftAssociative(): void
    {
        $binaryOpTreeRootLocator = new BinaryOpTreeRootLocator();

        // (Plus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $ab = new Plus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $ab);
        $b->setAttribute(AttributeKey::PARENT_NODE, $ab);
        $c = new Variable('c');
        $tree = new Plus($ab, $c);
        $ab->setAttribute(AttributeKey::PARENT_NODE, $tree);
        $c->setAttribute(AttributeKey::PARENT_NODE, $tree);

        $this->assertSame($tree, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($tree, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($tree, $binaryOpTreeRootLocator->findOperationRoot($ab, Plus::class));
        $this->assertSame($tree, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
    }

    public function testRightAssociative(): void
    {
        $binaryOpTreeRootLocator = new BinaryOpTreeRootLocator();

        // (Plus a (Plus b c))
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $bc = new Plus($b, $c);
        $b->setAttribute(AttributeKey::PARENT_NODE, $bc);
        $c->setAttribute(AttributeKey::PARENT_NODE, $bc);
        $tree = new Plus($a, $bc);
        $a->setAttribute(AttributeKey::PARENT_NODE, $tree);
        $bc->setAttribute(AttributeKey::PARENT_NODE, $tree);

        $this->assertSame($tree, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($bc, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($bc, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
        $this->assertSame($bc, $binaryOpTreeRootLocator->findOperationRoot($bc, Plus::class));
    }

    public function testWrongRootOp(): void
    {
        $binaryOpTreeRootLocator = new BinaryOpTreeRootLocator();

        // (Minus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $plus = new Plus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $plus);
        $b->setAttribute(AttributeKey::PARENT_NODE, $plus);
        $c = new Variable('c');
        $minus = new Minus($plus, $c);
        $plus->setAttribute(AttributeKey::PARENT_NODE, $minus);
        $c->setAttribute(AttributeKey::PARENT_NODE, $minus);

        $this->assertSame($plus, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($plus, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($plus, $binaryOpTreeRootLocator->findOperationRoot($plus, Plus::class));
        $this->assertSame($c, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
    }

    public function testInnerNodeDifferentOp(): void
    {
        $binaryOpTreeRootLocator = new BinaryOpTreeRootLocator();

        // (Plus (Minus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $minus = new Minus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $minus);
        $b->setAttribute(AttributeKey::PARENT_NODE, $minus);
        $plus = new Plus($minus, $c);
        $minus->setAttribute(AttributeKey::PARENT_NODE, $plus);
        $c->setAttribute(AttributeKey::PARENT_NODE, $plus);

        $this->assertSame($a, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($b, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($plus, $binaryOpTreeRootLocator->findOperationRoot($minus, Plus::class));
        $this->assertSame($plus, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
    }
}
