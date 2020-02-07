<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Visibility\ChangePropertyVisibilityRector;

use Iterator;
use Rector\Core\Rector\Visibility\ChangePropertyVisibilityRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

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
                    'Rector\Core\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture\NormalObject' => [
                        'toBePublicStaticProperty' => 'public',
                    ],
                ],
            ],
        ];
    }
}
