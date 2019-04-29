<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\PropertyToMethodRector;

use Rector\Rector\Property\PropertyToMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Property\PropertyToMethodRector\Source\Translator;

final class PropertyToMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [PropertyToMethodRector::class => [
            '$perClassPropertyToMethods' => [
                Translator::class => [
                    'locale' => [
                        'get' => 'getLocale',
                        'set' => 'setLocale',
                    ],
                ],
                'Rector\Tests\Rector\Property\PropertyToMethodRector\Fixture\SomeClassWithParameters' => [
                    'parameter' => [
                        'get' => [
                            'method' => 'getConfig',
                            'arguments' => ['parameter'],
                        ],
                    ],
                ],
            ],
        ]];
    }
}
