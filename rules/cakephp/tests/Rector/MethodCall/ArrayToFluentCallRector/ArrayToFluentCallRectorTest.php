<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector;

use Iterator;
use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source\ConfigurableClass;
use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source\FactoryClass;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrayToFluentCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArrayToFluentCallRector::class => [
                ArrayToFluentCallRector::CONFIGURABLE_CLASSES => [
                    ConfigurableClass::class => [
                        'name' => 'setName',
                        'size' => 'setSize',
                    ],
                ],
                ArrayToFluentCallRector::FACTORY_METHODS => [
                    FactoryClass::class => [
                        'buildClass' => [
                            'argumentPosition' => 2,
                            'class' => ConfigurableClass::class,
                        ],
                    ],
                ],
            ],
        ];
    }
}
