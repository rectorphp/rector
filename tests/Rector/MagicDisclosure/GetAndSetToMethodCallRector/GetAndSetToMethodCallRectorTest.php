<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector;

use Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

final class GetAndSetToMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc', __DIR__ . '/Fixture/fixture3.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return GetAndSetToMethodCallRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            SomeContainer::class => [
                'get' => 'getService',
                'set' => 'addService',
            ],
            'Enlight_View_Default' => [
                'get' => 'getService',
                'set' => 'addService',
            ]
        ];
    }
}
