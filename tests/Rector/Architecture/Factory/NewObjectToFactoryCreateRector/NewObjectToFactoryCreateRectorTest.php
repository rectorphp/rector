<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;

use Iterator;
use Rector\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;
use Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory;

final class NewObjectToFactoryCreateRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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
