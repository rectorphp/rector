<?php
declare(strict_types=1);

namespace Rector\Testing\PHPUnit\Behavior;

use Rector\Testing\PHPUnit\RunnableRectorFactory;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @property-read RunnableRectorFactory $runnableRectorFactory
 */
trait RunnableTestTrait
{
    protected function assertOriginalAndFixedFileResultEquals(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo
    ): void {
        $runnable = $this->runnableRectorFactory->createRunnableClass($originalFileInfo);

        $actualResult = $runnable->run();
        $expectedInstance = $this->runnableRectorFactory->createRunnableClass($expectedFileInfo);
        $expectedResult = $expectedInstance->run();

        $this->assertSame($expectedResult, $actualResult);
    }
}
