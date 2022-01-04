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
        $locator = new BinaryOpTreeRootLocator();

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

        $this->assertSame($tree, $locator->findOperationRoot($a, Plus::class));
        $this->assertSame($tree, $locator->findOperationRoot($b, Plus::class));
        $this->assertSame($tree, $locator->findOperationRoot($ab, Plus::class));
        $this->assertSame($tree, $locator->findOperationRoot($c, Plus::class));
    }

    public function testRightAssociative(): void
    {
        $locator = new BinaryOpTreeRootLocator();

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

        $this->assertSame($tree, $locator->findOperationRoot($a, Plus::class));
        $this->assertSame($bc, $locator->findOperationRoot($b, Plus::class));
        $this->assertSame($bc, $locator->findOperationRoot($c, Plus::class));
        $this->assertSame($bc, $locator->findOperationRoot($bc, Plus::class));
    }

    public function testWrongRootOp(): void
    {
        $locator = new BinaryOpTreeRootLocator();

        // (Minus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $ab = new Plus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $ab);
        $b->setAttribute(AttributeKey::PARENT_NODE, $ab);
        $c = new Variable('c');
        $tree = new Minus($ab, $c);
        $ab->setAttribute(AttributeKey::PARENT_NODE, $tree);
        $c->setAttribute(AttributeKey::PARENT_NODE, $tree);

        $this->assertSame($ab, $locator->findOperationRoot($a, Plus::class));
        $this->assertSame($ab, $locator->findOperationRoot($b, Plus::class));
        $this->assertSame($ab, $locator->findOperationRoot($ab, Plus::class));
        $this->assertSame($c, $locator->findOperationRoot($c, Plus::class));
    }

    public function testInnerNodeDifferentOp(): void
    {
        $locator = new BinaryOpTreeRootLocator();

        // (Plus (Minus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $ab = new Minus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $ab);
        $b->setAttribute(AttributeKey::PARENT_NODE, $ab);
        $tree = new Plus($ab, $c);
        $ab->setAttribute(AttributeKey::PARENT_NODE, $tree);
        $c->setAttribute(AttributeKey::PARENT_NODE, $tree);

        $this->assertSame($a, $locator->findOperationRoot($a, Plus::class));
        $this->assertSame($b, $locator->findOperationRoot($b, Plus::class));
        $this->assertSame($tree, $locator->findOperationRoot($ab, Plus::class));
        $this->assertSame($tree, $locator->findOperationRoot($c, Plus::class));
    }
}
