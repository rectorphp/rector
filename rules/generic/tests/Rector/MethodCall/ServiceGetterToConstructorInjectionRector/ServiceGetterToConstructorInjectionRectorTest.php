<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Generic\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Generic\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ServiceGetterToConstructorInjectionRectorTest extends AbstractRectorTestCase
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

    protected function getPhpVersion(): string
    {
        return '7.2';
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ServiceGetterToConstructorInjectionRector::class => [
                ServiceGetterToConstructorInjectionRector::METHOD_NAMES_BY_TYPES_TO_SERVICE_TYPES => [
                    FirstService::class => [
                        'getAnotherService' => AnotherService::class,
                    ],
                ],
            ],
        ];
    }
}
