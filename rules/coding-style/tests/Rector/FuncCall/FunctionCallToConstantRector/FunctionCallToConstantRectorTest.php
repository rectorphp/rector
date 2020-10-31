<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FunctionCallToConstantRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FunctionCallToConstantRector::class => [
                FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => [
                    'php_sapi_name' => 'PHP_SAPI',
                    'pi' => 'M_PI',
                ],
            ],
        ];
    }
}
