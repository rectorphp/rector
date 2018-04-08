<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertInstanceOfComparisonRector extends AbstractPHPUnitRector
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
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertTrue' => 'assertInstanceOf',
        'assertFalse' => 'assertNotInstanceOf',
    ];

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        BuilderFactory $builderFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->builderFactory = $builderFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue($foo instanceof Foo, "message");',
                    '$this->assertFalse($foo instanceof Foo, "message");'
                ),
                new CodeSample(
                    '$this->assertInstanceOf("Foo", $foo, "message");',
                    '$this->assertNotInstanceOf("Foo", $foo, "message");'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods($node, array_keys($this->renameMethodsMap))) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;

        return $firstArgumentValue instanceof Instanceof_;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->identifierRenamer->renameNodeWithMap($methodCallNode, $this->renameMethodsMap);
        $this->changeOrderArguments($methodCallNode);

        return $methodCallNode;
    }

    public function changeOrderArguments(MethodCall $methodCallNode): void
    {
        $oldArguments = $methodCallNode->args;
        /** @var Instanceof_ $comparison */
        $comparison = $oldArguments[0]->value;

        $class = $comparison->class;
        $argument = $comparison->expr;

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge([
            $this->builderFactory->classConstFetch($class, 'class'),
            $argument,
        ], $oldArguments);
    }
}
