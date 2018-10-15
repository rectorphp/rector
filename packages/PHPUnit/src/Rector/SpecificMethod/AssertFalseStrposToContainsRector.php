<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertFalseStrposToContainsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertFalse' => 'assertNotContains',
        'assertNotFalse' => 'assertContains',
    ];

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    public function __construct(IdentifierRenamer $identifierRenamer, CallAnalyzer $callAnalyzer)
    {
        $this->identifierRenamer = $identifierRenamer;
        $this->callAnalyzer = $callAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertFalse(strpos($anything, "foo"), "message");',
                    '$this->assertNotContains("foo", $anything, "message");'
                ),
                new CodeSample(
                    '$this->assertNotFalse(stripos($anything, "foo"), "message");',
                    '$this->assertContains("foo", $anything, "message");'
                ),
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
        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }

        if (! $this->isNames($methodCallNode, array_keys($this->renameMethodsMap))) {
            return null;
        }

        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $this->callAnalyzer->isNames($firstArgumentValue, ['strpos', 'stripos'])) {
            return null;
        }

        $this->identifierRenamer->renameNodeWithMap($methodCallNode, $this->renameMethodsMap);
        $this->changeOrderArguments($methodCallNode);

        return $methodCallNode;
    }

    public function changeOrderArguments(MethodCall $methodCallNode): void
    {
        $oldArguments = $methodCallNode->args;

        /** @var Identifier $oldArguments */
        $strposArguments = $oldArguments[0]->value;

        $firstArgument = $strposArguments->args[1];
        $secondArgument = $strposArguments->args[0];

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge([$firstArgument, $secondArgument], $oldArguments);
    }
}
