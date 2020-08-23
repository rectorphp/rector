<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PHPUnit\ValueObject\ConstantWithAssertMethods;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector\AssertSameBoolNullToSpecificMethodRectorTest
 */
final class AssertSameBoolNullToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var ConstantWithAssertMethods[]
     */
    private $constantWithAssertMethods = [];

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(IdentifierManipulator $identifierManipulator)
    {
        $this->identifierManipulator = $identifierManipulator;

        $this->constantWithAssertMethods = [
            new ConstantWithAssertMethods('null', 'assertNull', 'assertNotNull'),
            new ConstantWithAssertMethods('true', 'assertTrue', 'assertNotTrue'),
            new ConstantWithAssertMethods('false', 'assertFalse', 'assertNotFalse'),
        ];
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample('$this->assertSame(null, $anything);', '$this->assertNull($anything);'),
                new CodeSample('$this->assertNotSame(false, $anything);', '$this->assertNotFalse($anything);'),
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
        if (! $this->isPHPUnitMethodNames($node, ['assertSame', 'assertNotSame'])) {
            return null;
        }
        $firstArgumentValue = $node->args[0]->value;
        if (! $firstArgumentValue instanceof ConstFetch) {
            return null;
        }

        foreach ($this->constantWithAssertMethods as $constantWithAssertMethod) {
            if (! $this->isName($firstArgumentValue, $constantWithAssertMethod->getConstant())) {
                continue;
            }

            $this->renameMethod($node, $constantWithAssertMethod);
            $this->moveArguments($node);

            return $node;
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function renameMethod(Node $node, ConstantWithAssertMethods $constantWithAssertMethods): void
    {
        $this->identifierManipulator->renameNodeWithMap($node, [
            'assertSame' => $constantWithAssertMethods->getAssetMethodName(),
            'assertNotSame' => $constantWithAssertMethods->getNotAssertMethodName(),
        ]);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function moveArguments(Node $node): void
    {
        $methodArguments = $node->args;
        array_shift($methodArguments);

        $node->args = $methodArguments;
    }
}
