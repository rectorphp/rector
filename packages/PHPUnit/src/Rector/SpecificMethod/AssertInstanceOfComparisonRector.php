<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertInstanceOfComparisonRector extends AbstractPHPUnitRector
{
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

    public function __construct(IdentifierRenamer $identifierRenamer, BuilderFactory $builderFactory)
    {
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

        if (! $this->isNames($node, array_keys($this->renameMethodsMap))) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if ($firstArgumentValue instanceof Instanceof_ === false) {
            return null;
        }
        $this->identifierRenamer->renameNodeWithMap($node, $this->renameMethodsMap);
        $this->changeOrderArguments($node);

        return $node;
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
            new Arg($this->builderFactory->classConstFetch($class, 'class')),
            new Arg($argument),
        ], $oldArguments);
    }
}
