<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use Rector\PhpParser\Node\Maintainer\IdentifierMaintainer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertInstanceOfComparisonRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertTrue' => 'assertInstanceOf',
        'assertFalse' => 'assertNotInstanceOf',
    ];

    /**
     * @var IdentifierMaintainer
     */
    private $IdentifierMaintainer;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(IdentifierMaintainer $IdentifierMaintainer, BuilderFactory $builderFactory)
    {
        $this->IdentifierMaintainer = $IdentifierMaintainer;
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
        if (! $firstArgumentValue instanceof Instanceof_) {
            return null;
        }
        $this->IdentifierMaintainer->renameNodeWithMap($node, $this->renameMethodsMap);
        $this->changeOrderArguments($node);

        return $node;
    }

    public function changeOrderArguments(MethodCall $methodCall): void
    {
        $oldArguments = $methodCall->args;
        /** @var Instanceof_ $comparison */
        $comparison = $oldArguments[0]->value;

        $class = $comparison->class;
        $argument = $comparison->expr;

        unset($oldArguments[0]);

        $methodCall->args = array_merge([
            new Arg($this->builderFactory->classConstFetch($class, 'class')),
            new Arg($argument),
        ], $oldArguments);
    }
}
