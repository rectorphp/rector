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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, ['assertSame', 'assertNotSame'])) {
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
