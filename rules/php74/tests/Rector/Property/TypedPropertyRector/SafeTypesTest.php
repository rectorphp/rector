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
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->setParameter(Option::SAFE_TYPES, true);
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSafeTypes');
    }

    /**
     * @return array<string, array<string, bool>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            TypedPropertyRector::class => [
                TypedPropertyRector::CLASS_LIKE_TYPE_ONLY => false,
            ],
        ];
    }
}
