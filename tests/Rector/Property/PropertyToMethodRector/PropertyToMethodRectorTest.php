<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Property\PropertyToMethodRector;

use Iterator;
use Rector\Core\Rector\Property\PropertyToMethodRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Property\PropertyToMethodRector\Source\Translator;

final class PropertyToMethodRectorTest extends AbstractRectorTestCase
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
            PropertyToMethodRector::class => [
                '$perClassPropertyToMethods' => [
                    Translator::class => [
                        'locale' => [
                            'get' => 'getLocale',
                            'set' => 'setLocale',
                        ],
                    ],
                    'Rector\Core\Tests\Rector\Property\PropertyToMethodRector\Fixture\SomeClassWithParameters' => [
                        'parameter' => [
                            'get' => [
                                'method' => 'getConfig',
                                'arguments' => ['parameter'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
