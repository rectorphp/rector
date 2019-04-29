<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector;

use Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\Klarka;
use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

final class GetAndSetToMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/get.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/klarka.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [GetAndSetToMethodCallRector::class => [
            '$typeToMethodCalls' => [
                SomeContainer::class => [
                    'get' => 'getService',
                    'set' => 'addService',
                ],
                'Enlight_View_Default' => [
                    'get' => 'getService',
                    'set' => 'addService',
                ],
                Klarka::class => [
                    'get' => 'get',
                ],
            ],
        ]];
    }
}
