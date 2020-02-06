<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\New_\NewToStaticCallRector;

use Iterator;
use Rector\Core\Rector\New_\NewToStaticCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Core\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;

final class NewToStaticCallRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NewToStaticCallRector::class => [
                '$typeToStaticCalls' => [
                    FromNewClass::class => [IntoStaticClass::class, 'run'],
                ],
            ],
        ];
    }
}
