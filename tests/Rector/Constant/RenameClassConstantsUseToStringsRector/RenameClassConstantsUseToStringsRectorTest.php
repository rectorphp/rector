<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Constant\RenameClassConstantsUseToStringsRector;

use Iterator;
use Rector\Core\Rector\Constant\RenameClassConstantsUseToStringsRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Constant\RenameClassConstantsUseToStringsRector\Source\OldClassWithConstants;

final class RenameClassConstantsUseToStringsRectorTest extends AbstractRectorTestCase
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
            RenameClassConstantsUseToStringsRector::class => [
                '$oldConstantsToNewValuesByType' => [
                    OldClassWithConstants::class => [
                        'DEVELOPMENT' => 'development',
                        'PRODUCTION' => 'production',
                    ],
                ],
            ],
        ];
    }
}
