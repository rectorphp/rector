<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\Class_\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollection;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DoctrineCodeQuality\Rector\Class_\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector;

final class ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionTest extends AbstractRectorTestCase
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
        return ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector::class;
    }
}
