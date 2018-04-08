<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
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
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var string
     */
    private $constantName;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, IdentifierRenamer $identifierRenamer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns same with null comparisons to their method name alternatives in PHPUnit Test Case', [
            new CodeSample('$this->assertSame(null, $anything);', '$this->assertNull($anything);'),
            new CodeSample('$this->assertNotSame(false, $anything);', '$this->assertNotFalse($anything);'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods($node, ['assertSame', 'assertNotSame'])) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof ConstFetch) {
            return false;
        }

        /** @var Identifier $constatName */
        $constatName = $firstArgumentValue->name;

        $this->constantName = $constatName->toLowerString();

        return isset($this->constValueToNewMethodNames[$this->constantName]);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
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
