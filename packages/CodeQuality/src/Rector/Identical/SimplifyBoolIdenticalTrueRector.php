<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector\SimplifyBoolIdenticalTrueRectorTest
 */
final class SimplifyBoolIdenticalTrueRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Symplify bool value compare to true or false', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE) === TRUE;
         $match = in_array($value, $items, TRUE) !== FALSE;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE);
         $match = in_array($value, $items, TRUE);
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isStaticType($node->left, BooleanType::class) && ! $this->isBool($node->left)) {
            return $this->processBoolTypeToNotBool($node, $node->left, $node->right);
        }

        if ($this->isStaticType($node->right, BooleanType::class) && ! $this->isBool($node->right)) {
            return $this->processBoolTypeToNotBool($node, $node->right, $node->left);
        }

        return null;
    }

    private function processBoolTypeToNotBool(Node $node, Expr $leftExpr, Expr $rightExpr): ?Expr
    {
        if ($node instanceof Identical) {
            if ($this->isTrue($rightExpr)) {
                return $leftExpr;
            }

            if ($this->isFalse($rightExpr)) {
                // prevent !!
                if ($leftExpr instanceof BooleanNot) {
                    return $leftExpr->expr;
                }

                return new BooleanNot($leftExpr);
            }
        }

        if ($node instanceof NotIdentical) {
            if ($this->isFalse($rightExpr)) {
                return $leftExpr;
            }

            if ($this->isTrue($rightExpr)) {
                return new BooleanNot($leftExpr);
            }
        }

        return null;
    }
}
