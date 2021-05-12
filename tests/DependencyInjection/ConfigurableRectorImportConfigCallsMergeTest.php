<?php

declare(strict_types=1);

namespace Rector\Core\Tests\DependencyInjection;

use Iterator;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ConfigurableRectorImportConfigCallsMergeTest extends AbstractRectorTestCase
{
    private PrivatesAccessor $privatesAccessor;

    protected function setUp(): void
    {
        $this->privatesAccessor = new PrivatesAccessor();
    }

    /**
     * @dataProvider provideData()
     * @param array<string, string> $expectedConfiguration
     */
    public function testMainConfigValues(string $config, array $expectedConfiguration): void
    {
        $rectorConfigsResolver = new RectorConfigsResolver();

        $configFileInfos = $rectorConfigsResolver->resolveFromConfigFileInfo(new SmartFileInfo($config));
        $this->bootFromConfigFileInfos($configFileInfos);

        /** @var RenameClassRector $renameClassRector */
        $renameClassRector = $this->getService(RenameClassRector::class);

        $oldToNewClasses = $this->privatesAccessor->getPrivateProperty($renameClassRector, 'oldToNewClasses');

        $this->assertSame($expectedConfiguration, $oldToNewClasses);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/config/main_config_with_only_imports.php', [
                'old_2' => 'new_2',
                'old_1' => 'new_1',
            ],
        ];

        yield [
            __DIR__ . '/config/main_config_with_override_value.php', [
                'old_2' => 'new_2',
                'old_1' => 'new_1',
                'old_4' => 'new_4',
            ],
        ];

        yield [
            __DIR__ . '/config/main_config_with_own_value.php', [
                'old_2' => 'new_2',
                'old_1' => 'new_1',
                'old_4' => 'new_4',
                'old_3' => 'new_3',
            ],
        ];

        yield [
            __DIR__ . '/config/one_set.php', [
                'PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\Framework\MockObject\Stub',
                'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\Framework\MockObject\Stub\ReturnStub',
                'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\Framework\MockObject\Matcher\Parameters',
                'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\Framework\MockObject\Matcher\Invocation',
                'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
                'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\Framework\MockObject\Invocation\ObjectInvocation',
            ],
        ];

        yield [
            __DIR__ . '/config/one_set_with_own_rename.php', [
                'Old' => 'New',
                'PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\Framework\MockObject\Stub',
                'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\Framework\MockObject\Stub\ReturnStub',
                'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\Framework\MockObject\Matcher\Parameters',
                'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\Framework\MockObject\Matcher\Invocation',
                'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
                'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\Framework\MockObject\Invocation\ObjectInvocation',
            ],
        ];

        yield [
            __DIR__ . '/config/two_sets.php', [
                'Twig_SimpleFilter' => 'Twig_Filter',
                'Twig_SimpleFunction' => 'Twig_Function',
                'Twig_SimpleTest' => 'Twig_Test',
                'PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\Framework\MockObject\Stub',
                'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\Framework\MockObject\Stub\ReturnStub',
                'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\Framework\MockObject\Matcher\Parameters',
                'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\Framework\MockObject\Matcher\Invocation',
                'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
                'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\Framework\MockObject\Invocation\ObjectInvocation',
            ],
        ];

        yield [
            __DIR__ . '/config/two_sets_with_own_rename.php', [
                'Old' => 'New',
                'Twig_SimpleFilter' => 'Twig_Filter',
                'Twig_SimpleFunction' => 'Twig_Function',
                'Twig_SimpleTest' => 'Twig_Test',
                'PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\Framework\MockObject\Stub',
                'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\Framework\MockObject\Stub\ReturnStub',
                'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\Framework\MockObject\Matcher\Parameters',
                'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\Framework\MockObject\Matcher\Invocation',
                'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
                'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\Framework\MockObject\Invocation\ObjectInvocation',
            ],
        ];
    }
}
