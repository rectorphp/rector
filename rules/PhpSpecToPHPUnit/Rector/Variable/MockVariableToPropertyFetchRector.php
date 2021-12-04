<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;

/**
 * $mock->call()
 * ↓
 * $this->mock->call()
 *
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class MockVariableToPropertyFetchRector extends AbstractPhpSpecToPHPUnitRector
{
    public function __construct(
        private readonly PhpSpecMockCollector $phpSpecMockCollector
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        if (! $this->isInPhpSpecBehavior($class)) {
            return null;
        }

        if (! $this->phpSpecMockCollector->isVariableMockInProperty($class, $node)) {
            return null;
        }

        /** @var string $variableName */
        $variableName = $this->getName($node);

        return new PropertyFetch(new Variable('this'), $variableName);
    }
}
