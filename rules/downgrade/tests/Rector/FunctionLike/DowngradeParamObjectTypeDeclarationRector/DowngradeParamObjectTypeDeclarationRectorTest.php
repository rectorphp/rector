<?php

declare(strict_types=1);

namespace Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;

use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Downgrade\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;

final class DowngradeParamObjectTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.2
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
            DowngradeParamObjectTypeDeclarationRector::class => [
                DowngradeParamObjectTypeDeclarationRector::ADD_DOC_BLOCK => true,
            ],
        ];
    }

    protected function getRectorClass(): string
    {
        return DowngradeParamObjectTypeDeclarationRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::BEFORE_OBJECT_TYPE;
    }
}
