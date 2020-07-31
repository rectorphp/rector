<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\ClassMethod\ReturnThisRemoveRector;
use Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\FixtureConfiguration\SomeClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ConfigurationTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureConfiguration');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReturnThisRemoveRector::class => [
                ReturnThisRemoveRector::TYPES_TO_MATCH => [SomeClass::class],
            ],
        ];
    }
}
