<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Naming\ExpectedNameResolver;

use Iterator;
use Rector\Naming\ExpectedNameResolver\InflectorSingularResolver;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class InflectorSingularResolverTest extends AbstractTestCase
{
    private InflectorSingularResolver $inflectorSingularResolver;

    protected function setUp(): void
    {
        $this->boot();
        $this->inflectorSingularResolver = $this->getService(InflectorSingularResolver::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function testResolveForForeach(string $currentName, string $expectedSingularName): void
    {
        $singularValue = $this->inflectorSingularResolver->resolve($currentName);
        $this->assertSame($expectedSingularName, $singularValue);
    }

    /**
     * @return Iterator<string[]>
     */
    public function provideData(): Iterator
    {
        yield ['psr4NamespacesToPaths', 'psr4NamespaceToPath'];
        yield ['nestedNews', 'nestedNew'];
        yield ['news', 'new'];
        yield ['argsOrOptions', 'argOrOption'];
    }
}
