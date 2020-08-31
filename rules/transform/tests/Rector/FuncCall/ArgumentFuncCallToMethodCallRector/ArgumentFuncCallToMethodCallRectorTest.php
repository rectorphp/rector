<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentFuncCallToMethodCallRectorTest extends AbstractRectorTestCase
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
     * @return array<string, array<ArgumentFuncCallToMethodCall[]|ArrayFuncCallToMethodCall[]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArgumentFuncCallToMethodCallRector::class => [
                ArgumentFuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => [
                    new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'make'),
                    new ArgumentFuncCallToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'route'),
                    new ArgumentFuncCallToMethodCall('back', 'Illuminate\Routing\Redirector', 'back', 'back'),
                    new ArgumentFuncCallToMethodCall(
                        'broadcast',
                        'Illuminate\Contracts\Broadcasting\Factory',
                        'event'
                    ),
                ],
                ArgumentFuncCallToMethodCallRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => [
                    new ArrayFuncCallToMethodCall('config', 'Illuminate\Contracts\Config\Repository', 'set', 'get'),
                    new ArrayFuncCallToMethodCall('session', 'Illuminate\Session\SessionManager', 'put', 'get'),
                ],
            ],
        ];
    }
}
