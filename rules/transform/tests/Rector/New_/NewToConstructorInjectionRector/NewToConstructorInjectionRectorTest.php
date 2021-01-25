<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\New_\NewToConstructorInjectionRector;
use Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewToConstructorInjectionRectorTest extends AbstractRectorTestCase
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
            NewToConstructorInjectionRector::class => [
                NewToConstructorInjectionRector::TYPES_TO_CONSTRUCTOR_INJECTION => [DummyValidator::class],
            ],
        ];
    }
}
