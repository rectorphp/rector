<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector;

use Iterator;
use Rector\Rector\Visibility\ChangePropertyVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

final class ChangePropertyVisibilityRectorTest extends AbstractRectorTestCase
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
            ChangePropertyVisibilityRector::class => [
                '$propertyToVisibilityByClass' => [
                    ParentObject::class => [
                        'toBePublicProperty' => 'public',
                        'toBeProtectedProperty' => 'protected',
                        'toBePrivateProperty' => 'private',
                        'toBePublicStaticProperty' => 'public',
                    ],
                    'Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture\NormalObject' => [
                        'toBePublicStaticProperty' => 'public',
                    ],
                ],
            ],
        ];
    }
}
