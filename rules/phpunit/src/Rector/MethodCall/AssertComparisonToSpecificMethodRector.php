<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

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
use PhpParser\Node\Scalar;
use Rector\Core\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PHPUnit\ValueObject\BinaryOpWithAssertMethod;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertComparisonToSpecificMethodRector\AssertComparisonToSpecificMethodRectorTest
 */
final class AssertComparisonToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var BinaryOpWithAssertMethod[]
     */
    private $binaryOpWithAssertMethods = [];

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(IdentifierManipulator $identifierManipulator)
    {
        $this->identifierManipulator = $identifierManipulator;

        $this->binaryOpWithAssertMethods = [
            new BinaryOpWithAssertMethod(Identical::class, 'assertSame', 'assertNotSame'),
            new BinaryOpWithAssertMethod(NotIdentical::class, 'assertNotSame', 'assertSame'),
            new BinaryOpWithAssertMethod(Equal::class, 'assertEquals', 'assertNotEquals'),
            new BinaryOpWithAssertMethod(NotEqual::class, 'assertNotEquals', 'assertEquals'),
            new BinaryOpWithAssertMethod(Greater::class, 'assertGreaterThan', 'assertLessThan'),
            new BinaryOpWithAssertMethod(Smaller::class, 'assertLessThan', 'assertGreaterThan'),
            new BinaryOpWithAssertMethod(
                GreaterOrEqual::class,
                'assertGreaterThanOrEqual',
                'assertLessThanOrEqual'
            ),
            new BinaryOpWithAssertMethod(
                SmallerOrEqual::class,
                'assertLessThanOrEqual',
                'assertGreaterThanOrEqual'
            ),
        ];
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
    private function processCallWithBinaryOp(Node $node, BinaryOp $binaryOp): ?Node
    {
        foreach ($this->binaryOpWithAssertMethods as $binaryOpWithAssertMethod) {
            if (get_class($binaryOp) !== $binaryOpWithAssertMethod->getBinaryOpClass()) {
                continue;
            }

            $this->identifierManipulator->renameNodeWithMap($node, [
                'assertTrue' => $binaryOpWithAssertMethod->getAssetMethodName(),
                'assertFalse' => $binaryOpWithAssertMethod->getNotAssertMethodName(),
            ]);

            $this->changeArgumentsOrder($node);

            return $node;
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function changeArgumentsOrder(Node $node): void
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
        $newArgs = [$firstArgument, $secondArgument];
        $node->args = $this->appendArgs($newArgs, $oldArguments);
    }

    private function isConstantValue(Node $node): bool
    {
        $nodeClass = get_class($node);
        if (in_array($nodeClass, [Array_::class, ConstFetch::class], true)) {
            return true;
        }

        if (is_subclass_of($node, Scalar::class)) {
            return true;
        }

        return $this->isVariableName($node, 'exp*');
    }
}
