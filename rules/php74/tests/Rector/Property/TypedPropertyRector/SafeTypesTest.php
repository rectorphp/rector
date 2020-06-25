<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SafeTypesTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->setParameter(Option::SAFE_TYPES, true);
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSafeTypes');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            TypedPropertyRector::class => [
                '$classLikeTypeOnly' => false,
            ],
        ];
    }
}
