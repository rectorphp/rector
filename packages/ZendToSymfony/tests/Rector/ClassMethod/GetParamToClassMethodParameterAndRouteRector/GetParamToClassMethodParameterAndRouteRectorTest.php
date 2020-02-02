<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\ZendToSymfony\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector;

final class GetParamToClassMethodParameterAndRouteRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return GetParamToClassMethodParameterAndRouteRector::class;
    }
}
