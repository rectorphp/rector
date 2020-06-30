<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\ClassMethod\ReturnThisRemoveRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReturnThisRemoveRectorTest extends AbstractRectorTestCase
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReturnThisRemoveRector::class => [
                '$classesToDefluent' => [
                    'Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture\SomeClass',
                    'Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture\SomeClassWithReturnAnnotations',
                ],
            ],
        ];
    }
}
