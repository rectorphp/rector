<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Assign\PropertyAssignToMethodCallRector;

use Iterator;
use Rector\Core\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;
use Rector\Core\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyAssignToMethodCallRectorTest extends AbstractRectorTestCase
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
            PropertyAssignToMethodCallRector::class => [
                '$oldPropertiesToNewMethodCallsByType' => [
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
