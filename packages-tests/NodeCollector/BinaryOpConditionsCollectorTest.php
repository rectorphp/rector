<?php

declare(strict_types=1);

namespace Rector\Tests\NodeCollector;

use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\Variable;
use PHPUnit\Framework\TestCase;
use Rector\NodeCollector\BinaryOpConditionsCollector;

final class BinaryOpConditionsCollectorTest extends TestCase
{
    public function testLeftAssociative(): void
    {
        $binaryOpConditionsCollector = new BinaryOpConditionsCollector();

        // (Plus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $plus = new Plus(new Plus($a, $b), $c);

        $result = $binaryOpConditionsCollector->findConditions($plus, Plus::class);

        $this->assertEquals([
            2 => $a,
            1 => $b,
            0 => $c,
        ], $result);
    }

    public function testRightAssociative(): void
    {
        $binaryOpConditionsCollector = new BinaryOpConditionsCollector();

        // (Plus a (Plus b c))
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $bc = new Plus($b, $c);
        $tree = new Plus($a, $bc);

        $result = $binaryOpConditionsCollector->findConditions($tree, Plus::class);

        $this->assertEquals([
            1 => $a,
            0 => $bc,
        ], $result);
    }

    public function testWrongRootOp(): void
    {
        $binaryOpConditionsCollector = new BinaryOpConditionsCollector();

        // (Minus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $minus = new Minus(new Plus($a, $b), $c);

        $result = $binaryOpConditionsCollector->findConditions($minus, Plus::class);

        $this->assertEquals([
            0 => $minus,
        ], $result);
    }

    public function testInnerNodeDifferentOp(): void
    {
        $binaryOpConditionsCollector = new BinaryOpConditionsCollector();

        // (Plus (Minus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $minus = new Minus($a, $b);
        $plus = new Plus($minus, $c);

        $result = $binaryOpConditionsCollector->findConditions($plus, Plus::class);

        $this->assertEquals([
            1 => $minus,
            0 => $c,
        ], $result);
    }
}
