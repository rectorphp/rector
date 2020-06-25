<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;

use Iterator;
use Rector\Core\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;
use Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewObjectToFactoryCreateRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
                '$objectToFactoryMethod' => [
                    MyClass::class => [
                        'class' => MyClassFactory::class,
                        'method' => 'create',
                    ],
                ],
            ],
        ];
    }
}
