<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector;

use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\Source\DummyObjectBehavior;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PhpSpecClassToPHPUnitClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/full_blown.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return PhpSpecClassToPHPUnitClassRector::class;
    }

    /**
     * @return mixed[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            '$objectBehaviorClass' => DummyObjectBehavior::class,
        ];
    }
}
