<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Rector\Core\Testing\Contract\RunnableInterface;
use Rector\Core\Testing\PHPUnit\Runnable\ClassLikeNamesSuffixer;
use Rector\Core\Testing\PHPUnit\Runnable\RunnableClassFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RunnableRectorFactory
{
    /**
     * @var RunnableClassFinder
     */
    private $runnableClassFinder;

    /**
     * @var ClassLikeNamesSuffixer
     */
    private $classLikeNamesSuffixer;

    public function __construct()
    {
        $this->runnableClassFinder = new RunnableClassFinder();
        $this->classLikeNamesSuffixer = new ClassLikeNamesSuffixer();
    }

    public function createRunnableClass(SmartFileInfo $classContentFileInfo): RunnableInterface
    {
        $temporaryPath = $this->createTemporaryPathWithPrefix($classContentFileInfo);

        $fileContent = $classContentFileInfo->getContents();
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

    private function createTemporaryPathWithPrefix(SmartFileInfo $smartFileInfo): string
    {
        // warning: if this hash is too short, the file can becom "identical"; took me 1 hour to find out
        $hash = Strings::substring(md5($smartFileInfo->getRealPath()), -15);

        return sprintf(sys_get_temp_dir() . '/_rector_runnable_%s_%s', $hash, $smartFileInfo->getBasename('.inc'));
    }
}
