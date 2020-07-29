<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;
use Rector\Generic\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;
use Rector\Generic\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory;
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
     * @return mixed[]
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
