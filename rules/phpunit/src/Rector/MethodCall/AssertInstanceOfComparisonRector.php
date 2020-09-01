<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertInstanceOfComparisonRector\AssertInstanceOfComparisonRectorTest
 */
final class AssertInstanceOfComparisonRector extends AbstractPHPUnitRector
{
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = [
        'assertTrue' => 'assertInstanceOf',
        'assertFalse' => 'assertNotInstanceOf',
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
            'Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue($foo instanceof Foo, "message");',
                    '$this->assertInstanceOf("Foo", $foo, "message");'
                ),
                new CodeSample(
                    '$this->assertFalse($foo instanceof Foo, "message");',
                    '$this->assertNotInstanceOf("Foo", $foo, "message");'
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
        $oldMethodNames = array_keys(self::RENAME_METHODS_MAP);
        if (! $this->isPHPUnitMethodNames($node, $oldMethodNames)) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if (! $firstArgumentValue instanceof Instanceof_) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        $this->changeArgumentsOrder($node);

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function changeArgumentsOrder(Node $node): void
    {
        $oldArguments = $node->args;
        /** @var Instanceof_ $comparison */
        $comparison = $oldArguments[0]->value;

        $class = $comparison->class;
        $argument = $comparison->expr;

        unset($oldArguments[0]);

        $node->args = array_merge([
            new Arg($this->builderFactory->classConstFetch($class, 'class')),
            new Arg($argument),
        ], $oldArguments);
    }
}
