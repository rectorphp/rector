<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;
use Rector\Transform\ValueObject\ArrayFunctionToMethodCall;
use Rector\Transform\ValueObject\FunctionToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class HelperFunctionToConstructorInjectionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            HelperFunctionToConstructorInjectionRector::class => [
                HelperFunctionToConstructorInjectionRector::FUNCTIONS_TO_METHOD_CALLS => [
                    new FunctionToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'viewFactory', 'make'),
                    new FunctionToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'route'),
                    new FunctionToMethodCall('back', 'Illuminate\Routing\Redirector', 'redirector', 'back', 'back'),
                    new FunctionToMethodCall(
                        'broadcast', 'Illuminate\Contracts\Broadcasting\Factory', 'broadcastFactory', 'event'
                    ),
                ],

                HelperFunctionToConstructorInjectionRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => [
                    new ArrayFunctionToMethodCall(
                        'config',
                        'Illuminate\Contracts\Config\Repository',
                        'configRepository',
                        'set',
                        'get'
                    ),
                    new ArrayFunctionToMethodCall(
                        'session',
                        'Illuminate\Session\SessionManager',
                        'sessionManager',
                        'put',
                        'get'
                    ),
                ],
            ],
        ];
    }
}
