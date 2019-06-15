<?php declare(strict_types=1);

namespace Rector\ElasticSearchDSL\Tests\Rector\MethodCall\MigrateFilterToQueryRector;

use Rector\ElasticSearchDSL\Rector\MethodCall\MigrateFilterToQueryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MigrateFilterToQueryRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return MigrateFilterToQueryRector::class;
    }
}
