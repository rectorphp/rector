<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;

use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector\Source\ForMethodsDummyObjectBehavior;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PhpSpecMocksToPHPUnitMocksRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return PhpSpecMocksToPHPUnitMocksRector::class;
    }

    /**
     * @return mixed[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            '$objectBehaviorClass' => ForMethodsDummyObjectBehavior::class,
        ];
    }
}
