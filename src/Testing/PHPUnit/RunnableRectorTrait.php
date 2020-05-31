<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Testing\Contract\RunnableInterface;
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

    private function getTemporaryClassName(): string
    {
        return 'ClassName_' . Random::generate(20);
    }

    private function createRunnableClass(SmartFileInfo $classFileInfo): RunnableInterface
    {
        $temporaryPath = $this->fixtureSplitter->createTemporaryPathWithPrefix($classFileInfo, 'runnable');

        $fileContent = $classFileInfo->getContents();

        // use unique class name for before and for after class, so both can be instantiated
        $className = $this->getTemporaryClassName();
        $classFileInfo = Strings::replace($fileContent, '#class\\s+(\\S*)\\s+#', sprintf('class %s ', $className));

        FileSystem::write($temporaryPath, $classFileInfo);
        include_once $temporaryPath;

        $matches = Strings::match($classFileInfo, '#\bnamespace (?<namespace>.*?);#');
        $namespace = $matches['namespace'] ?? '';

        $fullyQualifiedClassName = $namespace . '\\' . $className;

        if (! is_a($fullyQualifiedClassName, RunnableInterface::class, true)) {
            throw new ShouldNotHappenException();
        }

        return new $fullyQualifiedClassName();
    }
}
