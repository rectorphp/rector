<?php

declare(strict_types=1);

namespace Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector;

use Iterator;
use Rector\Core\ValueObject\Visibility;
use Rector\Visibility\Rector\Property\ChangePropertyVisibilityRector;
use Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangePropertyVisibilityRectorTest extends AbstractRectorTestCase
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ChangePropertyVisibilityRector::class => [
                ChangePropertyVisibilityRector::PROPERTY_TO_VISIBILITY_BY_CLASS => [
                    ParentObject::class => [
                        'toBePublicProperty' => Visibility::PUBLIC,
                        'toBeProtectedProperty' => Visibility::PROTECTED,
                        'toBePrivateProperty' => Visibility::PRIVATE,
                        'toBePublicStaticProperty' => Visibility::PUBLIC,
                    ],
                    'Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture\Fixture3' => [
                        'toBePublicStaticProperty' => Visibility::PUBLIC,
                    ],
                ],
            ],
        ];
    }
}
