<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;

use Iterator;
use Rector\Core\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ServiceGetterToConstructorInjectionRectorTest extends AbstractRectorTestCase
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
                '$methodNamesByTypesToServiceTypes' => [
                    FirstService::class => [
                        'getAnotherService' => AnotherService::class,
                    ],
                ],
            ],
        ];
    }
}
