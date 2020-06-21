<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Rector\Core\Testing\Contract\RunnableInterface;
use Rector\Core\Testing\PHPUnit\Runnable\ClassLikeNamesSuffixer;
use Rector\Core\Testing\PHPUnit\Runnable\RunnableClassFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RunnableRectorFactory
{
    /**
     * @var FixtureSplitter
     */
    private $fixtureSplitter;

    /**
     * @var RunnableClassFinder
     */
    private $runnableClassFinder;

    /**
     * @var ClassLikeNamesSuffixer
     */
    private $classLikeNamesSuffixer;

    public function __construct(FixtureSplitter $fixtureSplitter)
    {
        $this->fixtureSplitter = $fixtureSplitter;
        $this->runnableClassFinder = new RunnableClassFinder();
        $this->classLikeNamesSuffixer = new ClassLikeNamesSuffixer();
    }

    public function createRunnableClass(SmartFileInfo $classFileContent): RunnableInterface
    {
        $temporaryPath = $this->fixtureSplitter->createTemporaryPathWithPrefix($classFileContent, 'runnable');

        $fileContent = $classFileContent->getContents();
        $classNameSuffix = $this->getTemporaryClassSuffix();

        $suffixedFileContent = $this->classLikeNamesSuffixer->suffixContent($fileContent, $classNameSuffix);

        FileSystem::write($temporaryPath, $suffixedFileContent);
        include_once $temporaryPath;

        $runnableFullyQualifiedClassName = $this->runnableClassFinder->find($suffixedFileContent);

        return new $runnableFullyQualifiedClassName();
    }

    private function getTemporaryClassSuffix(): string
    {
        return Random::generate(30);
    }
}
