<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SymfonyPhpConfig\Rector\MethodCall\ChangeCallByNameToConstantByClassRector;
use Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\Source\IdClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeCallByNameToConstantByClassRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            ChangeCallByNameToConstantByClassRector::class => [
                ChangeCallByNameToConstantByClassRector::CLASS_TYPES_TO_METHOD_NAME => [
                    IdClass::class => 'configure',
                ],
            ],
        ];
    }
}
