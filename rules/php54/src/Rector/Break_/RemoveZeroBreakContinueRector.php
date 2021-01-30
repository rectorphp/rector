<?php

declare(strict_types=1);

namespace Rector\Php54\Rector\Break_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ConstantType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.php.net/manual/en/control-structures.continue.php
 * @see https://www.php.net/manual/en/control-structures.break.php
 *
 * @see \Rector\Php54\Tests\Rector\Break_\RemoveZeroBreakContinueRector\RemoveZeroBreakContinueRectorTest
 */
final class RemoveZeroBreakContinueRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove 0 from break and continue', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($random)
    {
        continue 0;
        break 0;

        $five = 5;
        continue $five;

        break $random;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($random)
    {
        continue;
        break;

        $five = 5;
        continue 5;

        break;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Break_::class, Continue_::class];
    }

    /**
     * @param Break_|Continue_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->num === null) {
            return null;
        }

        if ($node->num instanceof LNumber) {
            $number = $this->valueResolver->getValue($node->num);
            if ($number > 1) {
                return null;
            }

            if ($number === 0) {
                $node->num = null;
                return $node;
            }

            return null;
        }

        if ($node->num instanceof Variable) {
            return $this->processVariableNum($node, $node->num);
        }

        return null;
    }

    /**
     * @param Break_|Continue_ $node
     */
    private function processVariableNum(Node $node, Variable $numVariable): ?Node
    {
        $staticType = $this->getStaticType($numVariable);

        if ($staticType instanceof ConstantType) {
            if ($staticType instanceof ConstantIntegerType) {
                if ($staticType->getValue() === 0) {
                    $node->num = null;
                    return $node;
                }

                if ($staticType->getValue() > 0) {
                    $node->num = new LNumber($staticType->getValue());
                    return $node;
                }
            }

            return $node;
        }

        // remove variable
        $node->num = null;

        return null;
    }
}
