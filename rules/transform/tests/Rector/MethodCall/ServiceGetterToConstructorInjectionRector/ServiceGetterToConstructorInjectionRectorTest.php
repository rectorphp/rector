<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;

use Iterator;
use Rector\Core\Util\StaticPhpVersion;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
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

    protected function getPhpVersion(): int
    {
        return StaticPhpVersion::getIntVersion('7.2');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ServiceGetterToConstructorInjectionRector::class => [
                ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => [
                    new ServiceGetterToConstructorInjection(
                        FirstService::class,
                        'getAnotherService',
                        AnotherService::class
                    ),
                ],
            ],
        ];
    }
}
