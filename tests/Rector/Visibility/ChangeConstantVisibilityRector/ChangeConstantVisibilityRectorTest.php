<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Visibility\ChangeConstantVisibilityRector;

use Iterator;
use Rector\Core\Rector\Visibility\ChangeConstantVisibilityRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Visibility\ChangeConstantVisibilityRector\Source\ParentObject;

final class ChangeConstantVisibilityRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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
            ChangeConstantVisibilityRector::class => [
                '$constantToVisibilityByClass' => [
                    ParentObject::class => [
                        'TO_BE_PUBLIC_CONSTANT' => 'public',
                        'TO_BE_PROTECTED_CONSTANT' => 'protected',
                        'TO_BE_PRIVATE_CONSTANT' => 'private',
                    ],
                    'Rector\Core\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\AnotherClassWithInvalidConstants' => [
                        'TO_BE_PRIVATE_CONSTANT' => 'private',
                    ],
                ],
            ],
        ];
    }
}
