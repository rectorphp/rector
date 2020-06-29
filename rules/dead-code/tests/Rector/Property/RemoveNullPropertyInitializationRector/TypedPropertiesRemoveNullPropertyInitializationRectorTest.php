<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveNullPropertyInitializationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\Rector\Property\RemoveNullPropertyInitializationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TypedPropertiesRemoveNullPropertyInitializationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @requires PHP >= 7.4
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureTypedProperties');
    }

    protected function getRectorClass(): string
    {
        return RemoveNullPropertyInitializationRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
