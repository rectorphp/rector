<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php54\Rector\Break_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Stmt\Break_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Continue_;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantIntegerType;
use RectorPrefix20220606\PHPStan\Type\ConstantType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/control-structures.continue.php https://www.php.net/manual/en/control-structures.break.php
 *
 * @see \Rector\Tests\Php54\Rector\Break_\RemoveZeroBreakContinueRector\RemoveZeroBreakContinueRectorTest
 */
final class RemoveZeroBreakContinueRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_ZERO_BREAK;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove 0 from break and continue', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Break_::class, Continue_::class];
    }
    /**
     * @param Break_|Continue_ $node
     */
    public function refactor(Node $node) : ?Node
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
     * @param \PhpParser\Node\Stmt\Break_|\PhpParser\Node\Stmt\Continue_ $stmt
     */
    private function processVariableNum($stmt, Variable $numVariable) : ?Node
    {
        $staticType = $this->getType($numVariable);
        if ($staticType instanceof ConstantType) {
            if ($staticType instanceof ConstantIntegerType) {
                if ($staticType->getValue() === 0) {
                    $stmt->num = null;
                    return $stmt;
                }
                if ($staticType->getValue() > 0) {
                    $stmt->num = new LNumber($staticType->getValue());
                    return $stmt;
                }
            }
            return $stmt;
        }
        // remove variable
        $stmt->num = null;
        return null;
    }
}
