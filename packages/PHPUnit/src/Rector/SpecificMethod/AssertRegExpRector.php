<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertRegExpRector extends AbstractPHPUnitRector
{
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
            'Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase',
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

        if (! $this->isNames($node, ['assertSame', 'assertEquals', 'assertNotSame', 'assertNotEquals'])) {
            return null;
        }

        /** @var FuncCall $secondArgumentValue */
        $secondArgumentValue = $node->args[1]->value;

        if (! $this->isName($secondArgumentValue, 'preg_match')) {
            return null;
        }

        $oldMethodName = $this->callAnalyzer->resolveName($node);
        $oldCondition = null;

        $oldFirstArgument = $node->args[0]->value;
        $oldCondition = $this->resolveOldCondition($oldFirstArgument);

        $this->renameMethod($node, $oldMethodName, $oldCondition);
        $this->moveFunctionArgumentsUp($node);

        return $node;
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

    private function resolveOldCondition(Node $node): int
    {
        if ($node instanceof LNumber) {
            return $node->value;
        }

        if ($node instanceof ConstFetch) {
            /** @var Identifier $constFetchName */
            $constFetchName = $node->name;

            return $constFetchName->toLowerString() === 'true' ? 1 : 0;
        }
    }

    private function renameMethod(Node $methodCallNode, string $oldMethodName, int $oldCondition): void
    {
        if (in_array($oldMethodName, ['assertSame', 'assertEquals'], true) && $oldCondition === 1
            || in_array($oldMethodName, ['assertNotSame', 'assertNotEquals'], true) && $oldCondition === 0
        ) {
            $this->identifierRenamer->renameNode($methodCallNode, 'assertRegExp');
        }

        if (in_array($oldMethodName, ['assertSame', 'assertEquals'], true) && $oldCondition === 0
            || in_array($oldMethodName, ['assertNotSame', 'assertNotEquals'], true) && $oldCondition === 1
        ) {
            $this->identifierRenamer->renameNode($methodCallNode, 'assertNotRegExp');
        }
    }
}
