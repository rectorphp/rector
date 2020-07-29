<?php

declare(strict_types=1);

namespace Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Decouple\Rector\DecoupleClassMethodToOwnClassRector;
use Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Source\AbstractFather;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DecoupleClassMethodToOwnClassRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo, string $expectedFilePath, string $expectedContentFilePath): void
    {
        $this->doTestFileInfo($fileInfo);

        $this->assertFileExists($expectedFilePath);
        $this->assertFileEquals($expectedContentFilePath, $expectedFilePath);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/basic.php.inc'),
            // expected new file location
            $this->getTempPath() . '/ExtraClassName.php',
            // expected new file content
            __DIR__ . '/Source/ExpectedExtraClassName.php',
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/with_property_dependency.php.inc'),
            // expected new file location
            $this->getTempPath() . '/ExtraUsingProperty.php',
            // expected new file content
            __DIR__ . '/Source/ExpectedExtraUsingProperty.php',
        ];
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            DecoupleClassMethodToOwnClassRector::class => [
                DecoupleClassMethodToOwnClassRector::METHOD_NAMES_BY_CLASS => [
                    'Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture\Basic' => [
                        'someMethod' => [
                            'method' => 'newMethodName',
                            'class' => 'ExtraClassName',
                            // optionally: "parent_class" =>
                        ],
                    ],
                    'Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture\WithPropertyDependency' => [
                        'usingProperty' => [
                            'method' => 'newUsingProperty',
                            'class' => 'ExtraUsingProperty',
                            'parent_class' => AbstractFather::class,
                        ],
                    ],
                ],
            ],
        ];
    }
}
