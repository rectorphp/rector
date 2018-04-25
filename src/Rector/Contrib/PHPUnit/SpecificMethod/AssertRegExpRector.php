<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertRegExpRector extends AbstractPHPUnitRector
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns preg_match() comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);',
                    '$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);'
                ),
                new CodeSample(
                    '$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);',
                    '$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods(
            $node,
            ['assertSame', 'assertEquals', 'assertNotSame', 'assertNotEquals']
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        /** @var FuncCall $secondArgumentValue */
        $secondArgumentValue = $methodCallNode->args[1]->value;
        if (! $this->isNamedFunction($secondArgumentValue)) {
            return false;
        }

        $methodName = $secondArgumentValue->name->toString();

        return $methodName === 'preg_match';
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $oldMethodName = $identifierNode->toString();
        $oldCondition = null;

        $oldFirstArgument = $methodCallNode->args[0]->value;
        if ($oldFirstArgument instanceof LNumber) {
            $oldCondition = $oldFirstArgument->value;
        } elseif ($oldFirstArgument instanceof ConstFetch) {
            /** @var Identifier $constFetchName */
            $constFetchName = $oldFirstArgument->name;
            $oldCondition = $constFetchName->toLowerString() === 'true' ? 1 : 0;
        }

        if (in_array($oldMethodName, ['assertSame', 'assertEquals'], true) && $oldCondition === 1
            || in_array($oldMethodName, ['assertNotSame', 'assertNotEquals'], true) && $oldCondition === 0
        ) {
            $this->identifierRenamer->renameNode($methodCallNode, 'assertRegExp');
        } elseif (in_array($oldMethodName, ['assertSame', 'assertEquals'], true) && $oldCondition === 0
            || in_array($oldMethodName, ['assertNotSame', 'assertNotEquals'], true) && $oldCondition === 1
        ) {
            $this->identifierRenamer->renameNode($methodCallNode, 'assertNotRegExp');
        }

        $this->moveFunctionArgumentsUp($methodCallNode);

        return $methodCallNode;
    }

    private function moveFunctionArgumentsUp(MethodCall $methodCallNode): void
    {
        $oldArguments = $methodCallNode->args;

        /** @var FuncCall $pregMatchFunction */
        $pregMatchFunction = $oldArguments[1]->value;
        [$regex, $variable] = $pregMatchFunction->args;

        unset($oldArguments[0], $oldArguments[1]);

        $methodCallNode->args = array_merge([$regex, $variable], $oldArguments);
    }

    private function isNamedFunction(Expr $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        $functionName = $node->name;
        return $functionName instanceof Name;
    }
}
