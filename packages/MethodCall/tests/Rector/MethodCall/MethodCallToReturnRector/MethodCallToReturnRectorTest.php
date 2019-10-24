<?php

declare(strict_types=1);

namespace Rector\MethodCall\Tests\Rector\MethodCall\MethodCallToReturnRector;

use Iterator;
use Rector\MethodCall\Rector\MethodCall\MethodCallToReturnRector;
use Rector\MethodCall\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MethodCallToReturnRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip_already_return.php.inc'];
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
