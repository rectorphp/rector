<?php

declare(strict_types=1);

namespace Rector\Downgrade\Tests\Rector\Property\NoDocBlockDowngradeTypedPropertyRector;

use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Downgrade\Rector\Property\DowngradeTypedPropertyRector;

final class DowngradeTypedPropertyRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.4
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
            DowngradeTypedPropertyRector::class => [
                DowngradeTypedPropertyRector::ADD_DOC_BLOCK => false,
            ],
        ];
    }

    protected function getRectorClass(): string
    {
        return DowngradeTypedPropertyRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::BEFORE_TYPED_PROPERTIES;
    }
}
