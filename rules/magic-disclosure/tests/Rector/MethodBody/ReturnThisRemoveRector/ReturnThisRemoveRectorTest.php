<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodBody\ReturnThisRemoveRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\MethodBody\ReturnThisRemoveRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReturnThisRemoveRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
                    'Rector\MagicDisclosure\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture\SomeClass',
                    'Rector\MagicDisclosure\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture\SomeClassWithReturnAnnotations',
                ],
            ],
        ];
    }
}
