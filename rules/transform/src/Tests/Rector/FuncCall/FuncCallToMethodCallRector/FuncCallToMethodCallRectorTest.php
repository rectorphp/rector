<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FuncCallToMethodCallRectorTest extends AbstractRectorTestCase
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
            FuncCallToMethodCallRector::class => [
                FuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => [
                    new FuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'viewFactory', 'make'),
                    new FuncCallToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'route'),
                    new FuncCallToMethodCall('back', 'Illuminate\Routing\Redirector', 'back', 'back'),
                    new FuncCallToMethodCall('broadcast', 'Illuminate\Contracts\Broadcasting\Factory', 'event'),
                ],

                FuncCallToMethodCallRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => [
                    new ArrayFuncCallToMethodCall('config', 'Illuminate\Contracts\Config\Repository', 'set', 'get'),
                    new ArrayFuncCallToMethodCall('session', 'Illuminate\Session\SessionManager', 'put', 'get'),
                ],
            ],
        ];
    }
}
