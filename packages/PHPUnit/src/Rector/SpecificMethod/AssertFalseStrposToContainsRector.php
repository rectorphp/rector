<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertFalseStrposToContainsRector extends AbstractPHPUnitRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertFalse' => 'assertNotContains',
        'assertNotFalse' => 'assertContains',
    ];

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, IdentifierRenamer $identifierRenamer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
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

    public function getNodeType(): string
    {
        return MethodCall::class;
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
        if (! $this->methodCallAnalyzer->isMethods($methodCallNode, array_keys($this->renameMethodsMap))) {
            return null;
        }
        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $this->isNamedFunction($firstArgumentValue)) {
            return null;
        }
        $strposNode = $firstArgumentValue->name->toString();
        if (in_array($strposNode, ['strpos', 'stripos'], true) === false) {
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

    private function isNamedFunction(Expr $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $node->name instanceof Name;
    }
}
