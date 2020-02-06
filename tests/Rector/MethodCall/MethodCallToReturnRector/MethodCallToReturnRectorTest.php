<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector;

use Iterator;
use Rector\Core\Rector\MethodCall\MethodCallToReturnRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

final class MethodCallToReturnRectorTest extends AbstractRectorTestCase
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
            MethodCallToReturnRector::class => [
                '$methodNamesByType' => [
                    ReturnDeny::class => ['deny'],
                ],
            ],
        ];
    }
}
