<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/4142
 * @see https://github.com/sebastianbergmann/phpunit/issues/4141
 * @see https://github.com/sebastianbergmann/phpunit/issues/4149
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector\AddProphecyTraitRectorTest
 */
final class AddProphecyTraitRector extends AbstractPHPUnitRector
{
    /**
     * @var string
     */
    private const PROPHECY_TRAIT = 'Prophecy\PhpUnit\ProphecyTrait';

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassInsertManipulator $classInsertManipulator, ClassManipulator $classManipulator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add Prophecy trait for method using $this->prophesize()', [
            new CodeSample(
                <<<'PHP'
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
PHP
,
                <<<'PHP'
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ExampleTest extends TestCase
{
    use ProphecyTrait;

    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }

        $this->classInsertManipulator->addAsFirstTrait($node, self::PROPHECY_TRAIT);

        return $node;
    }

    private function shouldSkipClass(Class_ $class): bool
    {
        if (! $this->isInTestClass($class)) {
            return true;
        }

        $hasProphesizeMethodCall = $this->hasProphesizeMethodCall($class);
        if (! $hasProphesizeMethodCall) {
            return true;
        }

        return $this->classManipulator->hasTrait($class, self::PROPHECY_TRAIT);
    }

    private function hasProphesizeMethodCall(Class_ $class): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class, function (Node $class): bool {
            return $this->isMethodCall($class, 'this', 'prophesize');
        });
    }
}
