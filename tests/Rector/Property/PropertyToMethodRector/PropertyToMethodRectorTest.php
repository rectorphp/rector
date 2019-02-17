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

    protected function getRectorClass(): string
    {
        return PropertyToMethodRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
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
        ];
    }
}
