<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\Assign\PropertyToMethodRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\Assign\PropertyToMethodRector;
use Rector\Transform\Tests\Rector\Assign\PropertyToMethodRector\Source\Translator;
use Rector\Transform\ValueObject\PropertyToMethod;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyToMethodRectorTest extends AbstractRectorTestCase
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
            PropertyToMethodRector::class => [
                PropertyToMethodRector::PROPERTIES_TO_METHOD_CALLS => [
                    new PropertyToMethod(Translator::class, 'locale', 'getLocale', 'setLocale'),
                    new PropertyToMethod(
                        'Rector\Transform\Tests\Rector\Assign\PropertyToMethodRector\Fixture\Fixture2',
                        'parameter',
                        'getConfig',
                        null,
                        ['parameter']
                    ),
                ],
            ],
        ];
    }
}
