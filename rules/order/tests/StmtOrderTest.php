<?php

declare(strict_types=1);

namespace Rector\Order\Tests;

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

    public function testCreateOldToNewKeys(): void
    {
        $desiredStmtOrder = ['first', 'second', 'third'];
        $currentStmtOrder = ['third', 'first', 'second'];
        $actual = $this->stmtOrder->createOldToNewKeys($desiredStmtOrder, $currentStmtOrder);

        $expected = [
            0 => 1,
            1 => 2,
            2 => 0,
        ];

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

        $this->assertEquals($expectedClass->stmts, $actualClass->stmts);
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
