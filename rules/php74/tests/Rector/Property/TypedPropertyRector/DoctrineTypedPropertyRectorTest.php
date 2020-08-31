<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DoctrineTypedPropertyRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureDoctrine');
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

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::BEFORE_UNION_TYPES;
    }
}
