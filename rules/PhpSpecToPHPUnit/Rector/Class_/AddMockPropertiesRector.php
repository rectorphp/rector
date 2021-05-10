<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;

/**
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class AddMockPropertiesRector extends AbstractPhpSpecToPHPUnitRector
{
    public function __construct(
        private ClassInsertManipulator $classInsertManipulator,
        private PhpSpecMockCollector $phpSpecMockCollector
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInPhpSpecBehavior($node)) {
            return null;
        }

        $classMocks = $this->phpSpecMockCollector->resolveClassMocksFromParam($node);

        /** @var string $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($classMocks as $name => $methods) {
            if ((is_countable($methods) ? count($methods) : 0) <= 1) {
                continue;
            }

            // non-ctor used mocks are probably local only
            if (! in_array('let', $methods, true)) {
                continue;
            }

            $this->phpSpecMockCollector->addPropertyMock($class, $name);

            $variableType = $this->phpSpecMockCollector->getTypeForClassAndVariable($node, $name);

            $unionType = new UnionType([
                new ObjectType($variableType),
                new ObjectType('PHPUnit\Framework\MockObject\MockObject'),
            ]);

            $this->classInsertManipulator->addPropertyToClass($node, $name, $unionType);
        }

        return null;
    }
}
