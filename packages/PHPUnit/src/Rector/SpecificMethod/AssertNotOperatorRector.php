<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertNotOperatorRector extends AbstractPHPUnitRector
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
        'assertTrue' => 'assertFalse',
        'assertFalse' => 'assertTrue',
    ];

    /**
     * @var StaticMethodCallAnalyzer
     */
    private $staticMethodCallAnalyzer;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample('$this->assertTrue(!$foo, "message");', '$this->assertFalse($foo, "message");'),
                new CodeSample('$this->assertFalse(!$foo, "message");', '$this->assertTrue($foo, "message");'),
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
        if (! $methodCallNode instanceof MethodCall && ! $methodCallNode instanceof StaticCall) {
            return null;
        }
        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }
        if (! $this->isNormalOrStaticMethods($methodCallNode)) {
            return null;
        }
        /** @var MethodCall $methodCallNode */
        $methodCallNode = $methodCallNode;
        $firstArgumentValue = $methodCallNode->args[0]->value;
        if ($firstArgumentValue instanceof BooleanNot === false) {
            return null;
        }
        $this->identifierRenamer->renameNodeWithMap($methodCallNode, $this->renameMethodsMap);

        $oldArguments = $methodCallNode->args;
        /** @var BooleanNot $negation */
        $negation = $oldArguments[0]->value;

        $expression = $negation->expr;

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge([new Arg($expression)], $oldArguments);

        return $methodCallNode;
    }

    private function isNormalOrStaticMethods(Node $node): bool
    {
        if ($this->methodCallAnalyzer->isMethods($node, array_keys($this->renameMethodsMap))) {
            return true;
        }

        if ($this->staticMethodCallAnalyzer->isMethods($node, array_keys($this->renameMethodsMap))) {
            return true;
        }

        return false;
    }
}
