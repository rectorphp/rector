<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;
use Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyAssignToMethodCallRectorTest extends AbstractRectorTestCase
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
            PropertyAssignToMethodCallRector::class => [
                PropertyAssignToMethodCallRector::OLD_PROPERTIES_TO_NEW_METHOD_CALLS_BY_TYPE => [
                    ChoiceControl::class => [
                        'checkAllowedValues' => 'checkDefaultValue',
                    ],
                    MultiChoiceControl::class => [
                        'checkAllowedValues' => 'checkDefaultValue',
                    ],
                ],
            ],
        ];
    }
}
