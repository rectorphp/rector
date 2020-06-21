<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Rector\Core\Testing\Contract\RunnableInterface;
use Rector\Core\Testing\PHPUnit\Runnable\ClassLikeNamesSuffixer;
use Rector\Core\Testing\PHPUnit\Runnable\RunnableClassFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @property FixtureSplitter $fixtureSplitter
 */
trait RunnableRectorTrait
{
    protected function assertOriginalAndFixedFileResultEquals(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo
    ): void {
        $originalInstance = $this->createRunnableClass($originalFileInfo);
        $expectedInstance = $this->createRunnableClass($expectedFileInfo);

        $actualResult = $originalInstance->run();
        $expectedResult = $expectedInstance->run();

        $this->assertSame($expectedResult, $actualResult);
    }

    private function getTemporaryClassSuffix(): string
    {
        return Random::generate(30);
    }

    private function createRunnableClass(SmartFileInfo $classFileContent): RunnableInterface
    {
        $temporaryPath = $this->fixtureSplitter->createTemporaryPathWithPrefix($classFileContent, 'runnable');

        $fileContent = $classFileContent->getContents();
        $classNameSuffix = $this->getTemporaryClassSuffix();

        $classNameSufixer = new ClassLikeNamesSuffixer();
        $suffixedFileContent = $classNameSufixer->suffixContent($fileContent, $classNameSuffix);

        FileSystem::write($temporaryPath, $suffixedFileContent);
        include_once $temporaryPath;

        $runnableClassFinder = new RunnableClassFinder();
        /** @noRector */
        $runnableFullyQualifiedClassName = $runnableClassFinder->find($suffixedFileContent);

        return new $runnableFullyQualifiedClassName();
    }
}
