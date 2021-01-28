<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture\Fixture2;
use Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyFetchToMethodCallRectorTest extends AbstractRectorTestCase
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
            PropertyFetchToMethodCallRector::class => [
                PropertyFetchToMethodCallRector::PROPERTIES_TO_METHOD_CALLS => [
                    new PropertyFetchToMethodCall(Translator::class, 'locale', 'getLocale', 'setLocale'),

                    new PropertyFetchToMethodCall('Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture\Fixture2', 'parameter', 'getConfig', null, ['parameter']),
                ],
            ],
        ];
    }
}
