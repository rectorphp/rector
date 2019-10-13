<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use Rector\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector\AssertComparisonToSpecificMethodRectorTest
 */
final class AssertComparisonToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private $defaultOldToNewMethods = [
        Identical::class => ['assertSame', 'assertNotSame'],
        NotIdentical::class => ['assertNotSame', 'assertSame'],
        Equal::class => ['assertEquals', 'assertNotEquals'],
        NotEqual::class => ['assertNotEquals', 'assertEquals'],
        Greater::class => ['assertGreaterThan', 'assertLessThan'],
        Smaller::class => ['assertLessThan', 'assertGreaterThan'],
        GreaterOrEqual::class => ['assertGreaterThanOrEqual', 'assertLessThanOrEqual'],
        SmallerOrEqual::class => ['assertLessThanOrEqual', 'assertGreaterThanOrEqual'],
    ];

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(IdentifierManipulator $identifierManipulator)
    {
        $this->identifierManipulator = $identifierManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns comparison operations to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue($foo === $bar, "message");',
                    '$this->assertSame($bar, $foo, "message");'
                ),
                new CodeSample(
                    '$this->assertFalse($foo >= $bar, "message");',
                    '$this->assertLessThanOrEqual($bar, $foo, "message");'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodNames($node, ['assertTrue', 'assertFalse'])) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if (! $firstArgumentValue instanceof BinaryOp) {
            return null;
        }

        return $this->processCallWithBinaryOp($node, $firstArgumentValue);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function changeOrderArguments(Node $node): void
    {
        $oldArguments = $node->args;

        /** @var BinaryOp $expression */
        $expression = $oldArguments[0]->value;

        if ($this->isConstantValue($expression->left)) {
            $firstArgument = new Arg($expression->left);
            $secondArgument = new Arg($expression->right);
        } else {
            $firstArgument = new Arg($expression->right);
            $secondArgument = new Arg($expression->left);
        }

        unset($oldArguments[0]);

        $node->args = array_merge([$firstArgument, $secondArgument], $oldArguments);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processCallWithBinaryOp(Node $node, BinaryOp $binaryOp): ?Node
    {
        $binaryOpClass = get_class($binaryOp);

        if (! isset($this->defaultOldToNewMethods[$binaryOpClass])) {
            return null;
        }

        [$trueMethodName, $falseMethodName] = $this->defaultOldToNewMethods[$binaryOpClass];
        $this->identifierManipulator->renameNodeWithMap($node, [
            'assertTrue' => $trueMethodName,
            'assertFalse' => $falseMethodName,
        ]);

        $this->changeOrderArguments($node);

        return $node;
    }

    private function isConstantValue(Node $node): bool
    {
        return in_array(get_class($node), [Array_::class, ConstFetch::class], true)
              || is_subclass_of($node, Scalar::class)
              || $node instanceof Variable && stripos((string) $this->getName($node), 'exp') === 0;
    }
}
