<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertSameBoolNullToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]|false[][]
     */
    private $constValueToNewMethodNames = [
        'null' => ['assertNull', 'assertNotNull'],
        'true' => ['assertTrue', 'assertNotTrue'],
        'false' => ['assertFalse', 'assertNotFalse'],
    ];

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var string
     */
    private $constantName;

    public function __construct(IdentifierRenamer $identifierRenamer)
    {
        $this->identifierRenamer = $identifierRenamer;
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $methodCallNode instanceof MethodCall) {
            return null;
        }
        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }
        if (! $this->isNames($methodCallNode, ['assertSame', 'assertNotSame'])) {
            return null;
        }
        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof ConstFetch) {
            return null;
        }
        /** @var Identifier $constatName */
        $constatName = $firstArgumentValue->name;
        $this->constantName = $constatName->toLowerString();
        if (isset($this->constValueToNewMethodNames[$this->constantName]) === false) {
            return null;
        }
        $this->renameMethod($methodCallNode);
        $this->moveArguments($methodCallNode);

        return $methodCallNode;
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        [$sameMethodName, $notSameMethodName] = $this->constValueToNewMethodNames[$this->constantName];

        $this->identifierRenamer->renameNodeWithMap($methodCallNode, [
            'assertSame' => $sameMethodName,
            'assertNotSame' => $notSameMethodName,
        ]);
    }

    private function moveArguments(MethodCall $methodCallNode): void
    {
        $methodArguments = $methodCallNode->args;
        array_shift($methodArguments);

        $methodCallNode->args = $methodArguments;
    }
}
