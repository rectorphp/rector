<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector;

use Nette\Utils\FileSystem;
use Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\NetteTesterTestCase;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteTesterClassToPHPUnitClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        // prepare dummy data
        FileSystem::copy(__DIR__ . '/Copy', $this->getTempPath());

        $this->doTestFiles([__DIR__ . '/Fixture/test_class.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return NetteTesterClassToPHPUnitClassRector::class;
    }

    /**
     * @return string[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            '$netteTesterTestCaseClass' => NetteTesterTestCase::class,
        ];
    }
}
