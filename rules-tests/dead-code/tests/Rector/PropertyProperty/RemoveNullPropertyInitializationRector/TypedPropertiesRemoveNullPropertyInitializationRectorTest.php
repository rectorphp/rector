<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TypedPropertiesRemoveNullPropertyInitializationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @requires PHP 7.4
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureTypedProperties');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/typed_properties.php';
    }
}
