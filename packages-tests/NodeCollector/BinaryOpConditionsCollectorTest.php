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
        $abcPlus = new Plus(new Plus($a, $b), $c);

        $result = $binaryOpConditionsCollector->findConditions($abcPlus, Plus::class);

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
        $bcPlus = new Plus($b, $c);
        $abcPlus = new Plus($a, $bcPlus);

        $result = $binaryOpConditionsCollector->findConditions($abcPlus, Plus::class);

        $this->assertEquals([
            1 => $a,
            0 => $bcPlus,
        ], $result);
    }

    public function testWrongRootOp(): void
    {
        $binaryOpConditionsCollector = new BinaryOpConditionsCollector();

        // (Minus (Plus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $abcMinus = new Minus(new Plus($a, $b), $c);

        $result = $binaryOpConditionsCollector->findConditions($abcMinus, Plus::class);

        $this->assertEquals([
            0 => $abcMinus,
        ], $result);
    }

    public function testTrivialCase(): void
    {
        $binaryOpConditionsCollector = new BinaryOpConditionsCollector();

        $variable = new Variable('a');

        $result = $binaryOpConditionsCollector->findConditions($variable, Plus::class);

        $this->assertEquals([
            0 => $variable,
        ], $result);
    }

    public function testInnerNodeDifferentOp(): void
    {
        $binaryOpConditionsCollector = new BinaryOpConditionsCollector();

        // (Plus (Minus a b) c)
        $a = new Variable('a');
        $b = new Variable('b');
        $c = new Variable('c');
        $abMinus = new Minus($a, $b);
        $abcPlus = new Plus($abMinus, $c);

        $result = $binaryOpConditionsCollector->findConditions($abcPlus, Plus::class);

        $this->assertEquals([
            1 => $abMinus,
            0 => $c,
        ], $result);
    }
}
