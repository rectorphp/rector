<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector;

use Iterator;
use Rector\Generic\Rector\New_\NewObjectToFactoryCreateRector;
use Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClass;
use Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClassFactory;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewObjectToFactoryCreateRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NewObjectToFactoryCreateRector::class => [
                NewObjectToFactoryCreateRector::OBJECT_TO_FACTORY_METHOD => [
                    MyClass::class => [
                        'class' => MyClassFactory::class,
                        'method' => 'create',
                    ],
                ],
            ],
        ];
    }
}
