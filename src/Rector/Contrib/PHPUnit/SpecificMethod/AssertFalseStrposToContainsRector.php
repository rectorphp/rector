<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertFalse(strpos($anything, 'foo'), 'message');
 * - $this->assertNotFalse(stripos($anything, 'foo'), 'message');
 *
 * After:
 * - $this->assertNotContains('foo', $anything, 'message');
 * - $this->assertContains('foo', $anything, 'message');
 */
final class AssertFalseStrposToContainsRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, IdentifierRenamer $identifierRenamer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            ['assertFalse', 'assertNotFalse']
        )) {
            return false;
        }

        $firstArgumentValue = $node->args[0]->value;

        if (! $firstArgumentValue instanceof FuncCall) {
            return false;
        }

        $strposNode = $firstArgumentValue->name->toString();

        return in_array($strposNode, ['strpos', 'stripos'], true);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->renameMethod($methodCallNode);
        $this->changeOrderArguments($methodCallNode);

        return $methodCallNode;
    }

    public function changeOrderArguments(MethodCall $methodCallNode): void
    {
        /** @var Identifier $oldArguments */
        $oldArguments = $methodCallNode->args;
        $strposArguments = $oldArguments[0]->value;

        $firstArgument = $strposArguments->args[1];
        $secondArgument = $strposArguments->args[0];

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge([
            $firstArgument, $secondArgument,
        ], $oldArguments);
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        $oldMethodName = $methodCallNode->name->toString();

        if ($oldMethodName === 'assertFalse') {
            $this->identifierRenamer->renameNode($methodCallNode, 'assertNotContains');
        } else {
            $this->identifierRenamer->renameNode($methodCallNode, 'assertContains');
        }
    }
}
