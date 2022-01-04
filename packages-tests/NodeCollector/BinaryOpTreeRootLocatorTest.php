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
        $abPlus = new Plus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $abPlus);
        $b->setAttribute(AttributeKey::PARENT_NODE, $abPlus);
        $c = new Variable('c');
        $abcPlus = new Plus($abPlus, $c);
        $abPlus->setAttribute(AttributeKey::PARENT_NODE, $abcPlus);
        $c->setAttribute(AttributeKey::PARENT_NODE, $abcPlus);

        $this->assertSame($abcPlus, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($abcPlus, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($abcPlus, $binaryOpTreeRootLocator->findOperationRoot($abPlus, Plus::class));
        $this->assertSame($abcPlus, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
    }

    public function testRightAssociative(): void
    {
        $binaryOpTreeRootLocator = new BinaryOpTreeRootLocator();

        // (Plus a (Plus b c))
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $bcPlus = new Plus($b, $c);
        $b->setAttribute(AttributeKey::PARENT_NODE, $bcPlus);
        $c->setAttribute(AttributeKey::PARENT_NODE, $bcPlus);
        $abcPlus = new Plus($a, $bcPlus);
        $a->setAttribute(AttributeKey::PARENT_NODE, $abcPlus);
        $bcPlus->setAttribute(AttributeKey::PARENT_NODE, $abcPlus);

        $this->assertSame($abcPlus, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($bcPlus, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($bcPlus, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
        $this->assertSame($bcPlus, $binaryOpTreeRootLocator->findOperationRoot($bcPlus, Plus::class));
    }

    public function testWrongRootOp(): void
    {
        $binaryOpTreeRootLocator = new BinaryOpTreeRootLocator();

        // (Minus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $abPlus = new Plus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $abPlus);
        $b->setAttribute(AttributeKey::PARENT_NODE, $abPlus);
        $c = new Variable('c');
        $abcMinus = new Minus($abPlus, $c);
        $abPlus->setAttribute(AttributeKey::PARENT_NODE, $abcMinus);
        $c->setAttribute(AttributeKey::PARENT_NODE, $abcMinus);

        $this->assertSame($abPlus, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($abPlus, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($abPlus, $binaryOpTreeRootLocator->findOperationRoot($abPlus, Plus::class));
        $this->assertSame($c, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
    }

    public function testInnerNodeDifferentOp(): void
    {
        $binaryOpTreeRootLocator = new BinaryOpTreeRootLocator();

        // (Plus (Minus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $abMinus = new Minus($a, $b);
        $a->setAttribute(AttributeKey::PARENT_NODE, $abMinus);
        $b->setAttribute(AttributeKey::PARENT_NODE, $abMinus);
        $abcPlus = new Plus($abMinus, $c);
        $abMinus->setAttribute(AttributeKey::PARENT_NODE, $abcPlus);
        $c->setAttribute(AttributeKey::PARENT_NODE, $abcPlus);

        $this->assertSame($a, $binaryOpTreeRootLocator->findOperationRoot($a, Plus::class));
        $this->assertSame($b, $binaryOpTreeRootLocator->findOperationRoot($b, Plus::class));
        $this->assertSame($abcPlus, $binaryOpTreeRootLocator->findOperationRoot($abMinus, Plus::class));
        $this->assertSame($abcPlus, $binaryOpTreeRootLocator->findOperationRoot($c, Plus::class));
    }
}
