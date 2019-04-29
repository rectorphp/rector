<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector;

use Rector\Rector\Interface_\MergeInterfacesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

final class MergeInterfacesRectorTest extends AbstractRectorTestCase
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
        return [MergeInterfacesRector::class => [
            '$oldToNewInterfaces' => [
                SomeOldInterface::class => SomeInterface::class,
            ],
        ]];
    }
}
