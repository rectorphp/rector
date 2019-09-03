<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector\AssertSameBoolNullToSpecificMethodRectorTest
 */
final class AssertSameBoolNullToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string
     */
    private $constantName;

    /**
     * @var string[][]
     */
    private $constValueToNewMethodNames = [
        'null' => ['assertNull', 'assertNotNull'],
        'true' => ['assertTrue', 'assertNotTrue'],
        'false' => ['assertFalse', 'assertNotFalse'],
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
        /** @var Identifier $constatName */
        $constatName = $firstArgumentValue->name;
        $this->constantName = $constatName->toLowerString();
        if (! isset($this->constValueToNewMethodNames[$this->constantName])) {
            return null;
        }
        $this->renameMethod($node);
        $this->moveArguments($node);

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function renameMethod(Node $node): void
    {
        [$sameMethodName, $notSameMethodName] = $this->constValueToNewMethodNames[$this->constantName];

        $this->identifierManipulator->renameNodeWithMap($node, [
            'assertSame' => $sameMethodName,
            'assertNotSame' => $notSameMethodName,
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
