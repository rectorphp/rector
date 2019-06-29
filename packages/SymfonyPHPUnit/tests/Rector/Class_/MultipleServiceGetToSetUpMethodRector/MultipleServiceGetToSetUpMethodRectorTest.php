<?php declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector;

use Rector\SymfonyPHPUnit\Rector\Class_\MultipleServiceGetToSetUpMethodRector;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\DummyKernelTestCase;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MultipleServiceGetToSetUpMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/existing_setup.php.inc',
            __DIR__ . '/Fixture/string_service_name.php.inc',
            __DIR__ . '/Fixture/extends_parent_class_with_property.php.inc',
            __DIR__ . '/Fixture/instant_call.php.inc',
            __DIR__ . '/Fixture/skip_sessions.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MultipleServiceGetToSetUpMethodRector::class => [
                '$kernelTestCaseClass' => DummyKernelTestCase::class,
            ],
        ];
    }
}
