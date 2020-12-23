<?php

declare(strict_types=1);

namespace Rector\Core\Tests\DependencyInjection;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ConfigurableRectorImportConfigCallsMergeTest extends AbstractKernelTestCase
{
    /**
     * @var RenameClassRector
     */
    private $renameClassRector;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    protected function setUp(): void
    {
        $this->privatesAccessor = new PrivatesAccessor();
    }

    /**
     * @dataProvider provideData()
     * @param array<string, string> $expectedConfiguration
     */
    public function testMainConfigValues(
        string $config,
        int $expectedConfigurationCount,
        array $expectedConfiguration
    ): void {
        $this->bootKernelWithConfigs(RectorKernel::class, [$config]);
        $this->renameClassRector = $this->getService(RenameClassRector::class);

        $oldToNewClasses = $this->privatesAccessor->getPrivateProperty($this->renameClassRector, 'oldToNewClasses');

        $this->assertCount($expectedConfigurationCount, $oldToNewClasses);
        $this->assertSame($expectedConfiguration, $oldToNewClasses);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/config/main_config_with_only_imports.php', 2, [
                'old_2' => 'new_2',
                'old_1' => 'new_1',
            ],
        ];

        yield [
            __DIR__ . '/config/main_config_with_own_value.php', 3, [
                'old_2' => 'new_2',
                'old_1' => 'new_1',
                'old_3' => 'new_3',
            ],
        ];

        yield [
            __DIR__ . '/config/main_config_with_override_value.php', 2, [
                'old_2' => 'new_2',
                'old_1' => 'new_1',
            ],
        ];
    }
}
