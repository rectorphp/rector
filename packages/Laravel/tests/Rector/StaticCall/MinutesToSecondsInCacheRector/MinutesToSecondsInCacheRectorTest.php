<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector;

use Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector;
use Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Source\LaravelStoreInterface;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MinutesToSecondsInCacheRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/skip_call.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MinutesToSecondsInCacheRector::class => [
                '$storeClass' => LaravelStoreInterface::class, // just for test case
            ],
        ];
    }
}
