<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector;

use Iterator;
use Rector\Rector\Visibility\ChangeMethodVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;

final class ChangeMethodVisibilityRectorTest extends AbstractRectorTestCase
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
            ChangeMethodVisibilityRector::class => [
                '$methodToVisibilityByClass' => [
                    ParentObject::class => [
                        'toBePublicMethod' => 'public',
                        'toBeProtectedMethod' => 'protected',
                        'toBePrivateMethod' => 'private',
                        'toBePublicStaticMethod' => 'public',
                    ],
                ],
            ],
        ];
    }
}
