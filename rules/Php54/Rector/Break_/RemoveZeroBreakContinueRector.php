<?php

declare (strict_types=1);
namespace Rector\Php54\Rector\Break_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ConstantType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php54\Rector\Break_\RemoveZeroBreakContinueRector\RemoveZeroBreakContinueRectorTest
 */
final class RemoveZeroBreakContinueRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
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
        if (!$node->num instanceof Expr) {
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
