<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;
use Rector\Transform\ValueObject\MethodCallToService;
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
                ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => [
                    new MethodCallToService(FirstService::class, 'getAnotherService', AnotherService::class),
                ],
            ],
        ];
    }
}
