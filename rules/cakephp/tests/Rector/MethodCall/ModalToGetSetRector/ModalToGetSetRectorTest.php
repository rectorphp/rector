<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

use Iterator;
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class ModalToGetSetRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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
            ModalToGetSetRector::class => [
                '$methodNamesByTypes' => [
                    SomeModelType::class => [
                        'config' => [
                            'get' => 'getConfig',
                            'minimal_argument_count' => 2,
                            'first_argument_type_to_set' => 'array',
                        ],
                        'customMethod' => [
                            'get' => 'customMethodGetName',
                            'set' => 'customMethodSetName',
                            'minimal_argument_count' => 2,
                            'first_argument_type_to_set' => 'array',
                        ],
                        'makeEntity' => [
                            'get' => 'createEntity',
                            'set' => 'generateEntity',
                        ],
                        'method' => null,
                    ],
                ],
            ],
        ];
    }
}
