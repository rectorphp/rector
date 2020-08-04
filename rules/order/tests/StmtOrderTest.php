<?php

declare(strict_types=1);

namespace Rector\Order\Tests;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Order\StmtOrder;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

class StmtOrderTest extends AbstractKernelTestCase
{
    /**
     * @var StmtOrder
     */
    private $stmtOrder;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->stmtOrder = self::$container->get(StmtOrder::class);
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
     */
    public function testCreateOldToNewKeys(array $desiredStmtOrder, array $currentStmtOrder, array $expected): void
    {
        $actual = $this->stmtOrder->createOldToNewKeys($desiredStmtOrder, $currentStmtOrder);
        $this->assertSame($expected, $actual);
    }

    public function testReorderClassStmtsByOldToNewKeys(): void
    {
        $oldToNewKeys = [
            0 => 0,
            1 => 2,
            2 => 1,
        ];

        $class = $this->getTestClassNode();

        $actualClass = $this->stmtOrder->reorderClassStmtsByOldToNewKeys($class, $oldToNewKeys);

        $expectedClass = $this->getExpectedClassNode();

        $this->assertSame($expectedClass->stmts, $actualClass->stmts);
    }

    private function getExpectedClassNode(): Class_
    {
        $expectedClass = new Class_('ExpectedClass');
        $expectedClass->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('name')]);
        $expectedClass->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('price')]);
        $expectedClass->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('service')]);
        return $expectedClass;
    }

    private function getTestClassNode(): Class_
    {
        $class = new Class_('ClassUnderTest');
        $class->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('name')]);
        $class->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('service')]);
        $class->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [new PropertyProperty('price')]);
        return $class;
    }
}
