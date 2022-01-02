<?php

declare(strict_types=1);

namespace Rector\Tests\NodeCollector;

use PHPUnit\Framework\TestCase;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\Variable;

final class BinaryOpConditionsCollectorTest extends TestCase
{
    public function testLeftAssociative(): void
    {
        $collector = new BinaryOpConditionsCollector();

        // (Plus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $tree = new Plus(new Plus($a, $b), $c);

        $result = $collector->findConditions($tree, Plus::class);

        $this->assertEquals([2 => $a, 1 => $b, 0 => $c], $result);
    }

    public function testRightAssociative(): void
    {
        $collector = new BinaryOpConditionsCollector();

        // (Plus a (Plus b c))
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $bc = new Plus($b, $c);
        $tree = new Plus($a, $bc);

        $result = $collector->findConditions($tree, Plus::class);

        $this->assertEquals([1 => $a, 0 => $bc], $result);
    }

    public function testWrongRootOp(): void
    {
        $collector = new BinaryOpConditionsCollector();

        // (Minus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $tree = new Minus(new Plus($a, $b), $c);

        $result = $collector->findConditions($tree, Plus::class);

        $this->assertEquals([0 => $tree], $result);
    }

    public function testInnerNodeDifferentOp(): void
    {
        $collector = new BinaryOpConditionsCollector();

        // (Plus (Minus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $ab = new Minus($a, $b);
        $tree = new Plus($ab, $c);

        $result = $collector->findConditions($tree, Plus::class);

        $this->assertEquals([1 => $ab, 0 => $c], $result);
    }
}
