<?php

declare(strict_types=1);

namespace Rector\Order\Tests;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Order\StmtOrder;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class StmtOrderTest extends AbstractKernelTestCase
{
    /**
     * @var int[]
     */
    private const OLD_TO_NEW_KEYS = [
        0 => 0,
        1 => 2,
        2 => 1,
    ];

    /**
     * @var StmtOrder
     */
    private $stmtOrder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->stmtOrder = $this->getService(StmtOrder::class);
        $this->nodeNameResolver = $this->getService(NodeNameResolver::class);
    }

    public function dataProvider(): Iterator
    {
        yield [
            ['first', 'second', 'third'],
            ['third', 'first', 'second'],
            [
                0 => 1,
                1 => 2,
                2 => 0,
            ],
        ];
        yield [
            ['first', 'second', 'third'],
            ['third', 'second', 'first'],
            [
                0 => 2,
                1 => 1,
                2 => 0,
            ],
        ];
        yield [
            ['first', 'second', 'third'],
            ['first', 'second', 'third'],
            [
                0 => 0,
                1 => 1,
                2 => 2,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param array<int, string> $desiredStmtOrder
     * @param array<int, string> $currentStmtOrder
     * @param array<int, int> $expected
     */
    public function testCreateOldToNewKeys(array $desiredStmtOrder, array $currentStmtOrder, array $expected): void
    {
        $actual = $this->stmtOrder->createOldToNewKeys($desiredStmtOrder, $currentStmtOrder);
        $this->assertSame($expected, $actual);
    }

    public function testReorderClassStmtsByOldToNewKeys(): void
    {
        $class = $this->getTestClassNode();
        $classLike = $this->stmtOrder->reorderClassStmtsByOldToNewKeys($class, self::OLD_TO_NEW_KEYS);
        $expectedClass = $this->getExpectedClassNode();

        $this->assertTrue($this->nodeNameResolver->areNamesEqual($expectedClass->stmts[0], $classLike->stmts[0]));

        $this->assertTrue($this->nodeNameResolver->areNamesEqual($expectedClass->stmts[1], $classLike->stmts[1]));

        $this->assertTrue($this->nodeNameResolver->areNamesEqual($expectedClass->stmts[2], $classLike->stmts[2]));
    }

    private function getTestClassNode(): Class_
    {
        $class = new Class_('ClassUnderTest');
        $class->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('name')]);
        $class->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('service')]);
        $class->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('price')]);
        return $class;
    }

    private function getExpectedClassNode(): Class_
    {
        $expectedClass = new Class_('ExpectedClass');
        $expectedClass->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('name')]);
        $expectedClass->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('price')]);
        $expectedClass->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('service')]);
        return $expectedClass;
    }
}
