<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector;

use Nette\Utils\FileSystem;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteTesterPHPUnitRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        // prepare dummy data
        FileSystem::copy(__DIR__ . '/Copy', $this->getTempPath());

        $this->doTestFiles([
            __DIR__ . '/Fixture/test_class.php.inc',
            __DIR__ . '/Fixture/assert_true.php.inc',
            __DIR__ . '/Fixture/assert_type.php.inc',
            __DIR__ . '/Fixture/various_asserts.php.inc',
            __DIR__ . '/Fixture/kdyby_tests_events.php.inc',
            __DIR__ . '/Fixture/data_provider.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
